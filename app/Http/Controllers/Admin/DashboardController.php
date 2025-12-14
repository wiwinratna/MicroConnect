<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
public function index()
{
    $start = \Carbon\Carbon::now()->startOfMonth()->toDateString();
    $end   = \Carbon\Carbon::now()->endOfMonth()->toDateString();

    $thresholdOmzet = 100000;   // batas omzet rendah
    $thresholdMargin = 10;      // margin rendah (%), opsional

    // cek kolom HPP di tabel produk (ubah kalau kolommu beda)
    $hppColumn = \Illuminate\Support\Facades\Schema::hasColumn('produk', 'hpp') ? 'hpp' : null;

    // Aggregate per UMKM bulan ini
    $base = \Illuminate\Support\Facades\DB::table('umkm as u')
        ->whereNotNull('u.nama_usaha')
        ->where('u.nama_usaha', '!=', '')
        ->leftJoin('penjualan as p', function ($join) use ($start, $end) {
            $join->on('p.umkm_id', '=', 'u.id')
                 ->whereBetween('p.tanggal', [$start, $end]);
        })
        ->leftJoin('penjualan_detail as pd', 'pd.penjualan_id', '=', 'p.id')
        ->leftJoin('produk as pr', 'pr.id', '=', 'pd.produk_id')
        ->selectRaw('
            u.id,
            u.nama_usaha,
            COALESCE(SUM(p.total),0) as omzet,
            COUNT(DISTINCT p.id) as trx,
            COALESCE(SUM(pd.qty),0) as qty_terjual,
            COALESCE(SUM(pd.subtotal),0) as subtotal_detail
        ')
        ->groupBy('u.id', 'u.nama_usaha');

    // kalau HPP ada, hitung profit & margin
    if ($hppColumn) {
        $base->addSelect(\Illuminate\Support\Facades\DB::raw(
            "COALESCE(SUM(pd.subtotal - (pd.qty * pr.$hppColumn)),0) as profit"
        ));
        $base->addSelect(\Illuminate\Support\Facades\DB::raw(
            "CASE WHEN COALESCE(SUM(p.total),0) > 0
                  THEN ROUND((COALESCE(SUM(pd.subtotal - (pd.qty * pr.$hppColumn)),0) / COALESCE(SUM(p.total),0)) * 100, 2)
                  ELSE NULL END as margin"
        ));
    } else {
        // kalau belum ada HPP, margin kita null biar di view jadi "-"
        $base->addSelect(\Illuminate\Support\Facades\DB::raw("NULL as profit"));
        $base->addSelect(\Illuminate\Support\Facades\DB::raw("NULL as margin"));
    }

    // 5 UMKM performa bagus (urut omzet desc)
    $topUmkm = (clone $base)
        ->orderByDesc('omzet')
        ->limit(5)
        ->get();

    // 5 UMKM perlu perhatian:
    // prioritas: trx=0 dulu, lalu omzet kecil
    $warningUmkm = (clone $base)
        ->havingRaw('COUNT(DISTINCT p.id)=0 OR COALESCE(SUM(p.total),0) <= ?', [$thresholdOmzet])
        ->orderByRaw('CASE WHEN COUNT(DISTINCT p.id)=0 THEN 0 ELSE 1 END')
        ->orderBy('omzet')
        ->limit(5)
        ->get();

    // tabel bawah (all UMKM) tetap boleh, tapi sekarang metriknya per UMKM
    $allUmkm = (clone $base)
        ->orderByDesc('omzet')
        ->paginate(10);

    return view('admin.dashboard', compact('topUmkm', 'warningUmkm', 'allUmkm', 'hppColumn'));
}
}
