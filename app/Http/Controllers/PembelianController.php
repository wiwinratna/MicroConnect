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

            // UPDATE STOK & HARGA RATA2
            $bahan = BahanBaku::find($bahanId);

            $stokLama = $bahan->stok_awal ?? 0;
            $hargaRataLama = $bahan->harga_rata2 ?? 0;

            $stokBaru = $stokLama + $qty;

            if ($stokBaru > 0) {
                $hargaRataBaru =
                    (($stokLama * $hargaRataLama) + ($qty * $harga))
                    / $stokBaru;
            } else {
                $hargaRataBaru = $harga;
            }

            $bahan->update([
                'stok_awal' => $stokBaru,
                'harga_rata2' => $hargaRataBaru,
            ]);
        }

        $pembelian->update(['total' => $total]);

        return redirect()
            ->route('umkm.pembelian.index')
            ->with('success', 'Pembelian berhasil disimpan dan stok diperbarui.');
    }
}
