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
            // 1) Buat header penjualan
            $penjualan = Penjualan::create([
                'umkm_id'        => $umkm->id,
                'tanggal'        => $tanggal,
                'kode_penjualan' => 'PJ-' . now()->format('YmdHis'),
                'pembeli'        => $pembeli,
                'catatan'        => $catatan,
                'total'          => 0,
            ]);

            return $this->applyImpacts($penjualan, $umkm, $items, $tanggal, $metodePembayaran, $pelangganId, $jatuhTempo, $catatan);
        });
    }

    /**
     * Revert all impacts of a sale (Stock, Journals, Piutang)
     */
    public function revertImpacts(Penjualan $penjualan): void
    {
        $umkm = $penjualan->umkm;

        // 1. Revert stok awal visual cache di BahanBaku
        $mutasiKeluar = StokMutasi::where('umkm_id', $penjualan->umkm_id)
            ->where('ref_tipe', 'penjualan')
            ->where('ref_id', $penjualan->id)
            ->where('jenis', 'KELUAR')
            ->get();

        foreach ($mutasiKeluar as $mutasi) {
            $bahan = BahanBaku::find($mutasi->bahan_id);
            if ($bahan) {
                $bahan->update([
                    'stok_awal' => ($bahan->stok_awal ?? 0) + $mutasi->qty
                ]);
            }
        }

        // 2. Hapus Jurnal Umum
        \App\Models\JurnalUmum::where('umkm_id', $penjualan->umkm_id)
            ->where('ref_tipe', 'penjualan')
            ->where('ref_id', $penjualan->id)
            ->delete();

        // 3. Hapus Stok Mutasi
        StokMutasi::where('umkm_id', $penjualan->umkm_id)
            ->where('ref_tipe', 'penjualan')
            ->where('ref_id', $penjualan->id)
            ->delete();

        // 4. Hapus Piutang
        \App\Models\Piutang::where('penjualan_id', $penjualan->id)->delete();

        // 5. Hapus Detail
        $penjualan->details()->delete();
    }

    /**
     * Apply all impacts to a sale header (old or new)
     */
    public function applyImpacts(
        Penjualan $penjualan,
        Umkm $umkm,
        array $items,
        string $tanggal,
        string $metodePembayaran,
        ?int $pelangganId = null,
        ?string $jatuhTempo = null,
        ?string $catatan = null
    ): array {
        $hasProducts = false;
        $total       = 0;
        $totalHppKeseluruhan = 0;

        $invService = new \App\Services\InventoryService();

        foreach ($items as $item) {
            $produkId = $item['produk_id'] ?? null;
            $qtyJual  = (float) ($item['qty'] ?? 0);

            if (!$produkId || $qtyJual <= 0) continue;

            $hasProducts = true;
            $produk = Produk::where('umkm_id', $umkm->id)->with('komposisi.bahan')->lockForUpdate()->findOrFail($produkId);
            
            $hargaJual = (float) ($produk->harga_jual ?? 0);
            $subtotal  = $hargaJual * $qtyJual;
            $total    += $subtotal;

            $hppProdukItem = 0;

            foreach ($produk->komposisi as $resep) {
                $bahan = $resep->bahan;
                if (!$bahan) continue;

                $qtyResepPerUnit  = (float) $resep->qty;
                $satuanResep      = $resep->satuan ?? $bahan->satuan;
                $satuanDasarBahan = $bahan->satuan;

                $qtyResepBase = UnitConverter::isCompatible($satuanResep, $satuanDasarBahan)
                    ? UnitConverter::convert($qtyResepPerUnit, $satuanResep, $satuanDasarBahan)
                    : $qtyResepPerUnit;

                $qtyBahanKeluar = $qtyResepBase * $qtyJual;
                $bahanLocked = BahanBaku::where('umkm_id', $umkm->id)->lockForUpdate()->findOrFail($bahan->id);

                $stokBahan = $invService->getStok($umkm->id, 'bahan', $bahanLocked->id);
                if ($stokBahan < $qtyBahanKeluar) {
                    throw new \Exception("Stok bahan '{$bahanLocked->nama_bahan}' tidak cukup untuk '{$produk->nama_produk}'.");
                }

                $hppBahan = $invService->hitungHpp($umkm, 'bahan', $bahanLocked->id, $qtyBahanKeluar);
                $hppProdukItem += $hppBahan;
                $hppPerUnit = $qtyBahanKeluar > 0 ? ($hppBahan / $qtyBahanKeluar) : 0;

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

                $bahanLocked->update(['stok_awal' => max(0, (float)($bahanLocked->stok_awal ?? 0) - $qtyBahanKeluar)]);
            }

            $totalHppKeseluruhan += $hppProdukItem;

            PenjualanDetail::create([
                'penjualan_id' => $penjualan->id,
                'produk_id'    => $produk->id,
                'qty'          => $qtyJual,
                'harga'        => $hargaJual,
                'subtotal'     => $subtotal,
            ]);
        }

        if (!$hasProducts) throw new \Exception("Minimal pilih 1 produk.");

        $penjualan->update(['total' => $total]);

        $piutangCreated = null;
        if ($metodePembayaran === 'piutang') {
            $pelanggan = \App\Models\Pelanggan::findOrFail($pelangganId);
            $piutangCreated = \App\Models\Piutang::create([
                'umkm_id'      => $umkm->id,
                'pelanggan_id' => $pelanggan->id,
                'penjualan_id' => $penjualan->id,
                'kode_piutang' => \App\Models\Piutang::generateKode(),
                'tanggal'      => $tanggal,
                'jatuh_tempo'  => $jatuhTempo,
                'nominal_awal' => $total,
                'sisa'         => $total,
                'sudah_dibayar'=> 0,
                'status'       => 'belum_lunas',
                'catatan'      => 'Penjualan No: ' . $penjualan->kode_penjualan . ($catatan ? ' (' . $catatan . ')' : ''),
            ]);
        }

        $accService = new \App\Services\AccountingService();
        $accService->jurnalPenjualan($umkm, $penjualan, $total, $totalHppKeseluruhan, ($metodePembayaran === 'piutang'), $piutangCreated);

        return ['penjualan' => $penjualan, 'piutang' => $piutangCreated];
    }
}
