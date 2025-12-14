<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;

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

            'produk_id.*' => 'nullable|integer',
            'qty.*'       => 'nullable|numeric|min:0.001',
        ]);

        try {
            DB::transaction(function () use ($request, $umkm) {

                // 1) Buat header penjualan
                $penjualan = Penjualan::create([
                    'umkm_id'        => $umkm->id,
                    'tanggal'        => $request->tanggal,
                    'kode_penjualan' => 'PJ-' . now()->format('YmdHis'),
                    'pembeli'        => $request->pembeli,
                    'catatan'        => $request->catatan,
                    'total'          => 0,
                ]);

                $total = 0;

                // 2) Loop item
                foreach ($request->produk_id ?? [] as $i => $produkId) {
                    if (!$produkId) continue;

                    $qtyJual = (float) ($request->qty[$i] ?? 0);
                    if ($qtyJual <= 0) continue;

                    // ======================
                    // Lock produk (stok aman)
                    // ======================
                    $produkLocked = Produk::where('umkm_id', $umkm->id)
                        ->lockForUpdate()
                        ->findOrFail($produkId);

                    $stokProdukNow = (float) ($produkLocked->stok ?? 0);
                    if ($stokProdukNow < $qtyJual) {
                        throw new \Exception(
                            "Stok produk '{$produkLocked->nama_produk}' tidak cukup. " .
                            "Butuh {$qtyJual}, stok {$stokProdukNow}."
                        );
                    }

                    // Ambil komposisi
                    $produkKomposisi = Produk::where('umkm_id', $umkm->id)
                        ->with('komposisi.bahan')
                        ->findOrFail($produkId);

                    // ======================
                    // (A) CEK STOK BAHAN
                    // ======================
                    foreach ($produkKomposisi->komposisi as $komp) {
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
                    foreach ($produkKomposisi->komposisi as $komp) {
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

                    // ======================
                    // (C) KURANGI STOK PRODUK + SIMPAN DETAIL
                    // ======================
                    $produkLocked->update([
                        'stok' => $stokProdukNow - $qtyJual
                    ]);

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

                // Update total header
                $penjualan->update(['total' => $total]);

                // ======================
                // (D) POSTING JURNAL (Dr Kas 111, Cr Pendapatan 400)
                // ======================
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

                $ket = 'Penjualan ' . ($penjualan->kode_penjualan ?? '-');
                if (!empty($penjualan->pembeli)) {
                    $ket .= ' - ' . $penjualan->pembeli;
                }

                // ---- Anti dobel jurnal ----
                // Kalau tabel jurnal_umum punya kolom ref_tipe & ref_id, pakai ini.
                // Kalau belum punya, ini akan error. Jadi kita cek dulu kolomnya lewat try.
                try {
                    JurnalUmum::where('ref_tipe', 'penjualan')
                        ->where('ref_id', $penjualan->id)
                        ->delete();

                    $useRef = true;
                } catch (\Throwable $e) {
                    $useRef = false;

                    // fallback: hapus berdasarkan keterangan (tidak sekuat ref, tapi aman untuk demo)
                    JurnalUmum::where('umkm_id', $umkm->id)
                        ->where('keterangan', 'like', 'Penjualan ' . $penjualan->kode_penjualan . '%')
                        ->delete();
                }

                $base = [
                    'umkm_id'    => $umkm->id,
                    'tanggal'    => $penjualan->tanggal,
                    'keterangan' => $ket,
                ];

                if ($useRef) {
                    $base['ref_tipe'] = 'penjualan';
                    $base['ref_id']   = $penjualan->id;
                }

                // Dr Kas
                JurnalUmum::create($base + [
                    'kode_akun' => $akunKas->kode_akun,
                    'nama_akun' => $akunKas->nama_akun,
                    'debit'     => $total,
                    'kredit'    => 0,
                ]);

                // Cr Pendapatan
                JurnalUmum::create($base + [
                    'kode_akun' => $akunPendapatan->kode_akun,
                    'nama_akun' => $akunPendapatan->nama_akun,
                    'debit'     => 0,
                    'kredit'    => $total,
                ]);
            });

            return redirect()
                ->route('umkm.penjualan.index')
                ->with('success', 'Penjualan berhasil disimpan. Stok produk & bahan otomatis berkurang + jurnal tercatat.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
