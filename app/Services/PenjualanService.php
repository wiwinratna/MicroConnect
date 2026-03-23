<?php

namespace App\Services;

use App\Models\Umkm;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\StokMutasi;
use App\Helpers\UnitConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenjualanService
{
    /**
     * Proses transaksi penjualan inti (digunakan oleh PenjualanController dan Mode Etalase).
     *
     * @param Umkm $umkm
     * @param array $items  Array of ['produk_id' => int, 'qty' => float]
     * @param string $tanggal
     * @param string $metodePembayaran  'tunai' atau 'piutang'
     * @param int|null $pelangganId
     * @param string|null $jatuhTempo
     * @param string|null $pembeli
     * @param string|null $catatan
     * @return array  ['penjualan' => Penjualan, 'piutang' => Piutang|null]
     * @throws \Exception
     */
    public function prosesTransaksi(
        Umkm $umkm,
        array $items,
        string $tanggal,
        string $metodePembayaran,
        ?int $pelangganId = null,
        ?string $jatuhTempo = null,
        ?string $pembeli = null,
        ?string $catatan = null
    ): array {
        return DB::transaction(function () use (
            $umkm, $items, $tanggal, $metodePembayaran, $pelangganId, $jatuhTempo, $pembeli, $catatan
        ) {
            // 1) Buat header penjualan (total diisi nanti)
            $penjualan = Penjualan::create([
                'umkm_id'        => $umkm->id,
                'tanggal'        => $tanggal,
                'kode_penjualan' => 'PJ-' . now()->format('YmdHis'),
                'pembeli'        => $pembeli,
                'catatan'        => $catatan,
                'total'          => 0,
            ]);

            $hasProducts = false;
            $total       = 0;
            $totalHppKeseluruhan = 0;

            $invService = new \App\Services\InventoryService();

            // 2) Loop setiap item penjualan
            foreach ($items as $item) {
                $produkId = $item['produk_id'] ?? null;
                $qtyJual  = (float) ($item['qty'] ?? 0);

                if (!$produkId || $qtyJual <= 0) {
                    continue;
                }

                $hasProducts = true;

                // Load produk + komposisi bahan
                $produk = Produk::where('umkm_id', $umkm->id)
                    ->with('komposisi.bahan')
                    ->lockForUpdate()
                    ->findOrFail($produkId);

                $komposisi = $produk->komposisi;

                // Peringatan: produk tanpa resep → HPP = 0
                if ($komposisi->isEmpty()) {
                    Log::warning("Penjualan: Produk '{$produk->nama_produk}' dijual tanpa komposisi resep. HPP = 0.");
                }

                // HARGA JUAL SELALU DIAMBIL DARI DATABASE (untuk keamanan dari client-side tampering)
                $hargaJual = (float) ($produk->harga_jual ?? 0);
                $subtotal  = $hargaJual * $qtyJual;
                $total    += $subtotal;

                $hppProdukItem = 0; // akumulasi HPP dari semua bahan untuk produk ini

                // 3) Proses setiap bahan dalam resep
                foreach ($komposisi as $resep) {
                    $bahan = $resep->bahan;
                    if (!$bahan) continue;

                    // Qty bahan untuk 1 unit produk × qty jual
                    $qtyResepPerUnit  = (float) $resep->qty;
                    $satuanResep      = $resep->satuan ?? $bahan->satuan;
                    $satuanDasarBahan = $bahan->satuan; // satuan dasar bahan baku

                    // Konversi ke satuan dasar bahan
                    $qtyResepBase = UnitConverter::isCompatible($satuanResep, $satuanDasarBahan)
                        ? UnitConverter::convert($qtyResepPerUnit, $satuanResep, $satuanDasarBahan)
                        : $qtyResepPerUnit;

                    $qtyBahanKeluar = $qtyResepBase * $qtyJual;

                    // Lock bahan baku untuk keamanan transaksi
                    $bahanLocked = BahanBaku::where('umkm_id', $umkm->id)
                        ->lockForUpdate()
                        ->findOrFail($bahan->id);

                    // Cek stok bahan cukup
                    $stokBahan = $invService->getStok($umkm->id, 'bahan', $bahanLocked->id);
                    if ($stokBahan < $qtyBahanKeluar) {
                        throw new \Exception(
                            "Stok bahan '{$bahanLocked->nama_bahan}' tidak cukup untuk memproduksi '{$produk->nama_produk}'. " .
                            "Butuh {$qtyBahanKeluar} {$satuanDasarBahan}, tersedia {$stokBahan} {$satuanDasarBahan}."
                        );
                    }

                    // Hitung HPP bahan berdasarkan metode FIFO/LIFO/Average
                    $hppBahan = $invService->hitungHpp($umkm, 'bahan', $bahanLocked->id, $qtyBahanKeluar);
                    $hppProdukItem += $hppBahan;

                    // Harga unit HPP untuk mutasi
                    $hppPerUnit = $qtyBahanKeluar > 0 ? ($hppBahan / $qtyBahanKeluar) : 0;

                    // Catat StokMutasi KELUAR untuk bahan
                    StokMutasi::create([
                        'umkm_id'   => $umkm->id,
                        'bahan_id'  => $bahanLocked->id,
                        'tanggal'   => $penjualan->tanggal,
                        'jenis'     => 'KELUAR',
                        'qty'       => $qtyBahanKeluar,
                        'harga_unit'=> $hppPerUnit,
                        'ref_tipe'  => 'penjualan',
                        'ref_id'    => $penjualan->id,
                    ]);

                    // Update cache stok bahan
                    $bahanLocked->update([
                        'stok_awal' => max(0, (float)($bahanLocked->stok_awal ?? 0) - $qtyBahanKeluar),
                    ]);
                }

                $totalHppKeseluruhan += $hppProdukItem;

                // 4) Simpan PenjualanDetail
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => $produk->id,
                    'qty'          => $qtyJual,
                    'harga'        => $hargaJual,
                    'subtotal'     => $subtotal,
                ]);
            }

            // Minimal 1 produk terpilih
            if (!$hasProducts) {
                throw new \Exception("Minimal pilih 1 produk dengan kuantitas lebih dari 0.");
            }

            // Update total header
            $penjualan->update(['total' => $total]);

            // 5) Buat Piutang jika pembayaran kredit
            $piutangCreated = null;
            if ($metodePembayaran === 'piutang') {
                $pelanggan = \App\Models\Pelanggan::findOrFail($pelangganId);

                $piutangCreated = \App\Models\Piutang::create([
                    'umkm_id'      => $umkm->id,
                    'pelanggan_id' => $pelanggan->id,
                    'kode_piutang' => \App\Models\Piutang::generateKode(),
                    'tanggal'      => $tanggal,
                    'jatuh_tempo'  => $jatuhTempo,
                    'nominal_awal' => $total,
                    'sisa'         => $total,
                    'sudah_dibayar'=> 0,
                    'status'       => 'belum_lunas',
                    'catatan'      => 'Penjualan No: ' . $penjualan->kode_penjualan .
                                     ($catatan ? ' (' . $catatan . ')' : ''),
                ]);

                $penjualan->update([
                    'catatan' => $penjualan->catatan . ' (Via Piutang: ' . $piutangCreated->kode_piutang . ')'
                ]);
            }

            // 6) Posting Jurnal via AccountingService
            $accService = new \App\Services\AccountingService();
            $accService->jurnalPenjualan(
                $umkm,
                $penjualan,
                $total,
                $totalHppKeseluruhan,
                ($metodePembayaran === 'piutang'),
                $piutangCreated
            );

            return [
                'penjualan' => $penjualan,
                'piutang'   => $piutangCreated
            ];
        });
    }
}
