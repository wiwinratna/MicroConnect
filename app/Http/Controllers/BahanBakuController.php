<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        $bahan = BahanBaku::where('umkm_id', $umkm->id)
            ->orderBy('nama_bahan')
            ->get();

        return view('umkm.bahan.index', compact('bahan'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        // kode baru untuk ditampilkan di form (readonly)
        $kodeBaru = BahanBaku::getKodeBahan();

        return view('umkm.bahan.create', compact('kodeBaru'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        $request->validate([
            'nama_bahan' => 'required|string|max:100',
            'satuan'     => 'required|string|max:30',
            'stok_awal'  => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        BahanBaku::create([
            'kode_bahan' => $request->kode_bahan ?? BahanBaku::getKodeBahan(),
            'umkm_id'    => $umkm->id,
            'nama_bahan' => $request->nama_bahan,
            'satuan'     => $request->satuan,
            'stok_awal'  => $request->stok_awal ?? 0,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        return view('umkm.bahan.edit', compact('bahan'));
    }

    public function update(Request $request, BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        $request->validate([
            'nama_bahan' => 'required|string|max:100',
            'satuan'     => 'required|string|max:30',
            'stok_awal'  => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $bahan->update([
            // kode_bahan TIDAK diubah
            'nama_bahan' => $request->nama_bahan,
            'satuan'     => $request->satuan,
            'stok_awal'  => $request->stok_awal ?? 0,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        $bahan->delete();

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
