<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\JurnalUmum;


class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
{
    $umkm = auth()->user()->umkm;

    $bulan = $request->get('bulan', now()->format('Y-m')); // contoh: 2025-12
    [$year, $month] = explode('-', $bulan);

    $jurnal = \App\Models\JurnalUmum::where('umkm_id', $umkm->id)
        ->whereYear('tanggal', $year)
        ->whereMonth('tanggal', $month)
        ->orderBy('tanggal')
        ->orderBy('id')
        ->get();

    return view('umkm.laporan.index', compact('bulan', 'jurnal'));
}
}
