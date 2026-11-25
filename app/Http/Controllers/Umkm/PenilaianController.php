<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\PenilaianLevel;

/**
 * Hasil penilaian untuk UMKM (FR-14). UMKM hanya melihat penilaian miliknya sendiri.
 */
class PenilaianController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $penilaian = $umkm
            ? PenilaianLevel::with(['detail.indikator', 'levelRekomendasi', 'levelAkhir'])
                ->where('umkm_id', $umkm->id)
                ->orderByDesc('tanggal_penilaian')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('umkm.penilaian.index', compact('penilaian', 'umkm'));
    }
}
