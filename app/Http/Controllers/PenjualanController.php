<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;
use App\Models\Coa;


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
            ->orderBy('nama_produk')
            ->get();

        return view('umkm.penjualan.create', compact('produk'));
    }

public function store(Request $request)
{
    $umkm = auth()->user()->umkm;

    $request->validate([
        'tanggal' => 'required|date',
        'pembeli' => 'nullable|string|max:100',
        'catatan' => 'nullable|string',

        'produk_id.*' => 'nullable|exists:produk,id',
        'qty.*'       => 'nullable|numeric|min:0.001',
    ]);

    try {
        DB::transaction(function () use ($request, $umkm) {

            $penjualan = Penjualan::create([
                'umkm_id'        => $umkm->id,
                'tanggal'        => $request->tanggal,
                'kode_penjualan' => 'PJ-' . now()->format('YmdHis'),
                'pembeli'        => $request->pembeli,
                'catatan'        => $request->catatan,
                'total'          => 0,
            ]);

            $total = 0;

            foreach ($request->produk_id ?? [] as $i => $produkId) {
                if (!$produkId) continue;

                $qtyJual = (float) ($request->qty[$i] ?? 0);
                if ($qtyJual <= 0) continue;

                // 🔒 Kunci produk dulu biar stok aman
                $produkLocked = Produk::where('umkm_id', $umkm->id)
                    ->lockForUpdate()
                    ->findOrFail($produkId);

                // ✅ CEK stok produk
                $stokProdukNow = (float) ($produkLocked->stok ?? 0);
                if ($stokProdukNow < $qtyJual) {
                    throw new \Exception(
                        "Stok produk '{$produkLocked->nama_produk}' tidak cukup. " .
                        "Butuh {$qtyJual}, stok {$stokProdukNow}."
                    );
                }

                // Ambil komposisi (boleh tanpa lock, karena bahan nanti di-lock)
                $produk = Produk::where('umkm_id', $umkm->id)
                    ->with('komposisi.bahan')
                    ->findOrFail($produkId);

                // ======================
                // (A) CEK STOK BAHAN
                // ======================
                foreach ($produk->komposisi as $komp) {
                    $bahan = $komp->bahan;
                    if (!$bahan) continue;

                    $butuh = (float) $komp->qty * $qtyJual;

                    $bahanLocked = BahanBaku::where('id', $bahan->id)
                        ->lockForUpdate()
                        ->first();

                    $stokNow = (float) ($bahanLocked->stok_awal ?? 0);

                    if ($stokNow < $butuh) {
                        throw new \Exception(
                            "Stok bahan '{$bahanLocked->nama_bahan}' tidak cukup. " .
                            "Butuh {$butuh} {$bahanLocked->satuan}, stok {$stokNow} {$bahanLocked->satuan}."
                        );
                    }
                }

                // ======================
                // (B) KURANGI STOK BAHAN
                // ======================
                foreach ($produk->komposisi as $komp) {
                    $bahan = $komp->bahan;
                    if (!$bahan) continue;

                    $butuh = (float) $komp->qty * $qtyJual;

                    $bahanLocked = BahanBaku::where('id', $bahan->id)
                        ->lockForUpdate()
                        ->first();

                    $stokNow = (float) ($bahanLocked->stok_awal ?? 0);

                    $bahanLocked->update([
                        'stok_awal' => $stokNow - $butuh
                    ]);
                }

                // ✅ (NEW) KURANGI STOK PRODUK
                $produkLocked->update([
                    'stok' => $stokProdukNow - $qtyJual
                ]);

                // ======================
                // (C) SIMPAN DETAIL
                // ======================
                $harga = (float) ($produkLocked->harga_jual ?? 0);
                $subtotal = $harga * $qtyJual;
                $total += $subtotal;

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => $produkLocked->id,
                    'qty'          => $qtyJual,
                    'harga'        => $harga,
                    'subtotal'     => $subtotal,
                ]);
            }

            $penjualan->update(['total' => $total]);

            // ======================
                // (D) POSTING JURNAL PENJUALAN (2 akun)
                // ======================

                // ambil akun dari COA
                $akunKas = DB::table('coa')
                    ->where('umkm_id', $umkm->id)
                    ->where('kode_akun', '111')
                    ->first();

                $akunPendapatan = DB::table('coa')
                    ->where('umkm_id', $umkm->id)
                    ->where('kode_akun', '400')
                    ->first();

                if (!$akunKas || !$akunPendapatan) {
                    throw new \Exception(
                        "COA belum lengkap untuk UMKM ini (umkm_id={$umkm->id}). " .
                        "Pastikan akun 111 (Kas) dan 400 (Pendapatan Penjualan) ada."
                    );
                }


                // biar gak dobel (kalau kolom ref_tipe & ref_id ada)
                JurnalUmum::where('ref_tipe', 'penjualan')
                    ->where('ref_id', $penjualan->id)
                    ->delete();

                $ket = 'Penjualan ' . ($penjualan->kode_penjualan ?? '-');
                if (!empty($penjualan->pembeli)) {
                    $ket .= ' - ' . $penjualan->pembeli;
                }

                // Dr Kas
                JurnalUmum::create([
                    'umkm_id'    => $umkm->id,
                    'tanggal'    => $penjualan->tanggal,
                    'kode_akun'  => $akunKas->kode_akun,
                    'nama_akun'  => $akunKas->nama_akun,
                    'keterangan' => $ket,
                    'debit'      => $total,
                    'kredit'     => 0,
                    'ref_tipe'   => 'penjualan',
                    'ref_id'     => $penjualan->id,
                ]);

                // Cr Pendapatan
                JurnalUmum::create([
                    'umkm_id'    => $umkm->id,
                    'tanggal'    => $penjualan->tanggal,
                    'kode_akun'  => $akunPendapatan->kode_akun,
                    'nama_akun'  => $akunPendapatan->nama_akun,
                    'keterangan' => $ket,
                    'debit'      => 0,
                    'kredit'     => $total,
                    'ref_tipe'   => 'penjualan',
                    'ref_id'     => $penjualan->id,
                ]);

        });

        return redirect()
            ->route('umkm.penjualan.index')
            ->with('success', 'Penjualan berhasil disimpan. Stok produk & bahan otomatis berkurang.');

    } catch (\Exception $e) {
        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}
}
