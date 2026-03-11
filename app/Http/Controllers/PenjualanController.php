<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\UnitConverter;

class PenjualanController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Penjualan::where('umkm_id', $umkm->id)
            ->orderByDesc('tanggal')
            ->get();

        return view('umkm.penjualan.index', compact('data'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        $produk = Produk::where('umkm_id', $umkm->id)
            ->with('komposisi.bahan')
            ->orderBy('nama_produk')
            ->get();

        $pelanggan = \App\Models\Pelanggan::where('umkm_id', $umkm->id)
            ->orderBy('nama_pelanggan')
            ->get();

        return view('umkm.penjualan.create', compact('produk', 'pelanggan'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'tanggal'           => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,piutang',
            'pembeli'           => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',
            'produk_id.*'       => 'nullable|integer',
            'qty.*'             => 'nullable|numeric|min:0.001',
            // Khusus piutang
            'pelanggan_id'      => 'required_if:metode_pembayaran,piutang|nullable|exists:pelanggan,id',
            'jatuh_tempo'       => 'required_if:metode_pembayaran,piutang|nullable|date',
        ]);

        try {
            DB::transaction(function () use ($request, $umkm) {

                // ============================================================
                // 1) Buat header penjualan (total diisi nanti)
                // ============================================================
                $penjualan = Penjualan::create([
                    'umkm_id'        => $umkm->id,
                    'tanggal'        => $request->tanggal,
                    'kode_penjualan' => 'PJ-' . now()->format('YmdHis'),
                    'pembeli'        => $request->pembeli,
                    'catatan'        => $request->catatan,
                    'total'          => 0,
                ]);

                $hasProducts = false;
                $total       = 0;
                $totalHppKeseluruhan = 0;

                $invService = new \App\Services\InventoryService();

                // ============================================================
                // 2) Loop setiap item penjualan
                // ============================================================
                foreach ($request->produk_id ?? [] as $i => $produkId) {
                    if (!$produkId) continue;

                    $qtyJual = (float) ($request->qty[$i] ?? 0);
                    if ($qtyJual <= 0) continue;
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

                    $hargaJual = (float) ($produk->harga_jual ?? 0);
                    $subtotal  = $hargaJual * $qtyJual;
                    $total    += $subtotal;

                    $hppProdukItem = 0; // akumulasi HPP dari semua bahan untuk produk ini

                    // ============================================================
                    // 3) Proses setiap bahan dalam resep
                    // ============================================================
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
                            : $qtyResepPerUnit; // fallback: pakai apa adanya jika tidak kompatibel

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

                        // Update cache stok bahan (field stok_awal digunakan sbg cache visual)
                        $bahanLocked->update([
                            'stok_awal' => max(0, (float)($bahanLocked->stok_awal ?? 0) - $qtyBahanKeluar),
                        ]);
                    }

                    $totalHppKeseluruhan += $hppProdukItem;

                    // ============================================================
                    // 4) Simpan PenjualanDetail
                    // ============================================================
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

                // ============================================================
                // 5) Buat Piutang jika pembayaran kredit
                // ============================================================
                $piutangCreated = null;
                if ($request->metode_pembayaran === 'piutang') {
                    $pelanggan = \App\Models\Pelanggan::findOrFail($request->pelanggan_id);

                    $piutangCreated = \App\Models\Piutang::create([
                        'umkm_id'      => $umkm->id,
                        'pelanggan_id' => $pelanggan->id,
                        'kode_piutang' => \App\Models\Piutang::generateKode(),
                        'tanggal'      => $request->tanggal,
                        'jatuh_tempo'  => $request->jatuh_tempo,
                        'nominal_awal' => $total,
                        'sisa'         => $total,
                        'sudah_dibayar'=> 0,
                        'status'       => 'belum_lunas',
                        'catatan'      => 'Penjualan No: ' . $penjualan->kode_penjualan .
                                         ($request->catatan ? ' (' . $request->catatan . ')' : ''),
                    ]);

                    $penjualan->update([
                        'catatan' => $penjualan->catatan . ' (Via Piutang: ' . $piutangCreated->kode_piutang . ')'
                    ]);
                }

                // ============================================================
                // 6) Posting Jurnal via AccountingService
                // ============================================================
                $accService = new \App\Services\AccountingService();
                $accService->jurnalPenjualan(
                    $umkm,
                    $penjualan,
                    $total,
                    $totalHppKeseluruhan,
                    ($request->metode_pembayaran === 'piutang'),
                    $piutangCreated
                );
            });

            $msg = 'Penjualan berhasil disimpan. Stok bahan baku otomatis berkurang sesuai resep produk dan jurnal tercatat.';
            if ($request->metode_pembayaran === 'piutang') {
                $msg .= ' Piutang baru telah dibuat.';
            }

            return redirect()
                ->route('umkm.penjualan.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Penjualan Error: ' . $e->getMessage(), [
                'umkm_id' => $umkm->id,
                'user_id' => auth()->id(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
