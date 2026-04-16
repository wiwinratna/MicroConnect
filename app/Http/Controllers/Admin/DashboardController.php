<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Umkm;
use App\Models\Ticket;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end   = Carbon::now()->endOfMonth()->toDateString();

        $thresholdOmzet = 100000;   // batas omzet rendah
        $thresholdMargin = 10;      // margin rendah (%)

        // 1. SUMMARY METRICS GLOBAL
        $totalUmkm = Umkm::count();
        $totalUmkmAktif = Umkm::where('status', 'aktif')->count();
        
        $levelCounts = DB::table('umkm as u')
            ->leftJoin('umkm_level as ul', 'u.level_id', '=', 'ul.id')
            ->select('ul.kode', DB::raw('count(u.id) as total'))
            ->groupBy('ul.kode')
            ->get()
            ->pluck('total', 'kode'); // e.g ['LVL1' => 10, 'LVL2' => 5]

        $openTickets = Schema::hasTable('tickets') ? Ticket::where('status', 'Open')->count() : 0;

        // 2. QUERY AGGREGATE PER UMKM
        // Untuk margin akurat di Level 2/3, idealnya: OMZET - HPP - BEBAN OPERASIONAL
        // Mari gabung penjualan (omzet, hpp) dan beban.
        
        // HPP Column protection
        $hppColumn = Schema::hasColumn('produk', 'hpp') ? 'pr.hpp' : '0';

        $base = DB::table('umkm as u')
            ->leftJoin('umkm_level as ul', 'u.level_id', '=', 'ul.id')
            ->whereNotNull('u.nama_usaha')
            ->whereRaw("u.nama_usaha != ''")
            ->select(
                'u.id',
                'u.nama_usaha',
                'u.created_at',
                DB::raw("COALESCE(ul.kode, 'NONE') as kode_level"),
                'ul.nama_level'
            )
            ->addSelect(DB::raw("
                (SELECT COALESCE(SUM(total), 0) FROM penjualan 
                 WHERE umkm_id = u.id AND tanggal BETWEEN '{$start}' AND '{$end}') as omzet
            "))
            ->addSelect(DB::raw("
                (SELECT COUNT(id) FROM penjualan 
                 WHERE umkm_id = u.id AND tanggal BETWEEN '{$start}' AND '{$end}') as trx
            "))
            ->addSelect(DB::raw("
                (SELECT COALESCE(SUM(pd.qty * {$hppColumn}), 0) 
                 FROM penjualan p
                 JOIN penjualan_detail pd ON p.id = pd.penjualan_id 
                 JOIN produk pr ON pr.id = pd.produk_id 
                 WHERE p.umkm_id = u.id AND p.tanggal BETWEEN '{$start}' AND '{$end}') as total_hpp
            "))
            ->addSelect(DB::raw("
                (SELECT COALESCE(SUM(j.debit), 0) 
                 FROM jurnal_umum j 
                 WHERE j.umkm_id = u.id AND j.kode_akun LIKE '6%' 
                 AND j.tanggal BETWEEN '{$start}' AND '{$end}') as beban_ops
            "));

        $umkmStats = clone $base;
        $umkmStats = $umkmStats->get()->map(function($item) use ($thresholdOmzet, $thresholdMargin) {
            
            // Hitung Profitabilitas
            $item->laba_bersih = $item->omzet - $item->total_hpp - $item->beban_ops;
            
            if ($item->omzet > 0) {
                $item->margin = round(($item->laba_bersih / $item->omzet) * 100, 2);
            } else {
                $item->margin = null;
            }

            // Hitung Status Kesehatan & Perhatian
            if ($item->trx == 0) {
                $item->status_kesehatan = 'Tidak Aktif';
                $item->badge_color = 'secondary';
                $item->score = 0; // untuk resighting
            } elseif ($item->omzet < $thresholdOmzet || ($item->margin !== null && $item->margin < $thresholdMargin)) {
                $item->status_kesehatan = 'Waspada / Perlu Perhatian';
                $item->badge_color = 'warning';
                $item->score = 1; 
            } else {
                $item->status_kesehatan = 'Sehat';
                $item->badge_color = 'success';
                $item->score = 2; // Makin tinggi makin bagus performanya (berdasar omzet+laba)
            }

            return $item;
        });

        // Hitung akumulasi omzet & laba dsb dari total item
        $totalOmzet = $umkmStats->sum('omzet');
        $umkmNolTrx = $umkmStats->where('trx', 0)->count();

        // Bagi kategori untuk blok dashboard
        // Performa Bagus (Top 5 Omzet dengan Status Sehat)
        $topUmkm = $umkmStats->where('score', 2)
                             ->sortByDesc('omzet')
                             ->take(5);

        // Perlu Perhatian (Waspada, atau trx=0) Top Priorities
        // Kita sorting yang score < 2. Utamakan yg ada trx tapi rugi.
        $warningUmkm = $umkmStats->where('score', '<', 2)
                                 ->sortBy('score') // 0 = tidak aktif, 1 = waspada
                                 ->sortBy('margin') // margin minus prio 1
                                 ->take(5);

        // Untuk tabel bawah, pagination manual
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $sortedItems = $umkmStats->sortByDesc('omzet')->values();
        
        $allUmkm = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedItems->forPage($currentPage, $perPage),
            $sortedItems->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('admin.dashboard', compact(
            'totalUmkmAktif', 'levelCounts', 'totalOmzet', 'umkmNolTrx', 'openTickets',
            'topUmkm', 'warningUmkm', 'allUmkm'
        ));
    }
}
