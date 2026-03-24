<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Pembelian::where('umkm_id', $umkm->id)
                          ->with('details.bahan')
                          ->orderByDesc('tanggal')
                          ->get();

        return view('umkm.pembelian.index', compact('data'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        $bahan = BahanBaku::where('umkm_id', $umkm->id)->get();
        $kode = Pembelian::generateKode();

        return view('umkm.pembelian.create', compact('bahan', 'kode'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        // VALIDAasi
        $request->validate([
            'tanggal'   => 'required|date',
            'nomor_nota'=> 'nullable|string|max:100',
            'supplier'  => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',

            'bahan_id.*'    => 'nullable|exists:bahan_baku,id',
            'qty.*'         => 'nullable|numeric|min:0.001',
            'harga_beli.*'  => 'nullable|numeric|min:0',
        ]);

        // SIMPAN HEADER
        $pembelian = Pembelian::create([
            'umkm_id'        => $umkm->id,
            'kode_pembelian' => Pembelian::generateKode(),
            'nomor_nota'     => $request->nomor_nota,
            'tanggal'        => $request->tanggal,
            'supplier'       => $request->supplier,
            'catatan'        => $request->catatan,
            'total'          => 0,
        ]);

        $total = 0;

        // SIMPAN DETAIL
        foreach ($request->bahan_id ?? [] as $i => $bahanId) {

            if (!$bahanId) continue;

            $qty = $request->qty[$i] ?? 0;
            $harga = $request->harga_beli[$i] ?? 0;

            if ($qty <= 0) continue;

            $subtotal = $qty * $harga;
            $total += $subtotal;

            PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'bahan_id'     => $bahanId,
                'qty'          => $qty,
                'harga_beli'   => $harga,
                'subtotal'     => $subtotal,
            ]);

            // MENCATAT MUTASI STOK BAHAN BAKU 
            \App\Models\StokMutasi::create([
                'umkm_id'      => $umkm->id,
                'bahan_id'     => $bahanId,
                'tanggal'      => $pembelian->tanggal,
                'jenis'        => 'MASUK',
                'qty'          => $qty,
                'harga_unit'   => $harga,
                'ref_tipe'     => 'pembelian',
                'ref_id'       => $pembelian->id,
                'ref_detail_id'=> $pembelian->id // Sementara pakai ID header, kalau butuh detail ID nanti disesuaikan
            ]);

            // STOK AWAL BahanBaku BISA DIKOSONGKAN atau DIUPDATE untuk caching.
            // Sesuai sistem, kita akan gunakan $bahan->stok_awal sebagai cache visual saja.
            $bahan = BahanBaku::find($bahanId);
            $stokBaru = ($bahan->stok_awal ?? 0) + $qty;
            
            $bahan->update([
                'stok_awal' => $stokBaru,
            ]);
        }

        $pembelian->update(['total' => $total]);

        // ======================
        // (E) DELEGATE POSTING JURNAL KE ACCOUNTING SERVICE
        // ======================
        $accService = new \App\Services\AccountingService();
        $accService->jurnalPembelian($umkm, $pembelian, $total);

        return redirect()
            ->route('umkm.pembelian.index')
            ->with('success', 'Pembelian berhasil disimpan, mutasi stok tercatat, dan jurnal otomatis terbentuk.');
    }
}
