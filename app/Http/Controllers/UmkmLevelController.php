<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\UmkmLevel;
use Illuminate\Http\Request;

class UmkmLevelController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // kalau sudah punya level, ga usah pilih lagi
        if ($user->umkm && $user->umkm->level_id) {
            return redirect()->route('umkm.dashboard');
        }

        $levels = UmkmLevel::all();

        return view('umkm.pilih-level', compact('levels'));
    }

    // misal di UmkmLevelController@store
    public function store(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:umkm_level,id',
        ]);

        $user = auth()->user();

        // cari umkm-nya user ini
        $umkm = \App\Models\Umkm::where('user_id', $user->id)->first();

        // kalau belum ada, baru create satu kali
        if (!$umkm) {
            $umkm = \App\Models\Umkm::create([
                'user_id' => $user->id,
                'level_id' => $request->level_id,
            ]);
        } else {
            $umkm->update([
                'level_id' => $request->level_id,
            ]);
        }

        // Seed COA default jika belum ada
        $umkm->seedDefaultCoa();

        return redirect()->route('umkm.dashboard')
            ->with('success', 'Level UMKM kamu berhasil disimpan.');
    }
}
