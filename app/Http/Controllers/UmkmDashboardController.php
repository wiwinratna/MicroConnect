<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;

class UmkmDashboardController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $today = now()->toDateString();
        $start7 = now()->subDays(6)->startOfDay()->toDateString(); // 7 hari termasuk hari ini
        $startMonth = now()->startOfMonth()->toDateString();
        $endMonth = now()->endOfMonth()->toDateString();

        // =========================
        // KPI CARDS
        // =========================
        $penjualanHariIni = (float) Penjualan::where('umkm_id', $umkm->id)
            ->whereDate('tanggal', $today)
            ->sum('total');

        $trxHariIni = (int) Penjualan::where('umkm_id', $umkm->id)
            ->whereDate('tanggal', $today)
            ->count();

        $penjualanBulanIni = (float) Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$startMonth, $endMonth])
            ->sum('total');

        $totalProduk = (int) Produk::where('umkm_id', $umkm->id)->count();
        $totalStokProduk = (float) Produk::where('umkm_id', $umkm->id)->sum('stok');

        // =========================
        // GRAFIK: PENJUALAN 7 HARI
        // =========================
        $sales7 = Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$start7, $today])
            ->selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl')
            ->toArray();

        // bikin label 7 hari full (biar tanggal kosong tampil 0)
        $labels7 = [];
        $data7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels7[] = $d;
            $data7[] = (float) ($sales7[$d] ?? 0);
        }

        // =========================
        // TOP 5 PRODUK TERLARIS BULAN INI (qty)
        // =========================
        $topProduk = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produk as pr', 'pr.id', '=', 'pd.produk_id')
            ->where('p.umkm_id', $umkm->id)
            ->whereBetween('p.tanggal', [$startMonth, $endMonth])
            ->selectRaw('pr.nama_produk as nama, SUM(pd.qty) as qty')
            ->groupBy('pr.nama_produk')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $topLabels = $topProduk->pluck('nama')->toArray();
        $topData = $topProduk->pluck('qty')->map(fn($v) => (float)$v)->toArray();

        // =========================
        // ALERT: STOK MENIPIS
        // =========================
        $batasBahan = 5;  // kamu bisa ganti
        $batasProduk = 5; // kamu bisa ganti

        $bahanMenipis = BahanBaku::where('umkm_id', $umkm->id)
            ->where('stok_awal', '<', $batasBahan)
            ->orderBy('stok_awal')
            ->limit(6)
            ->get();

        $produkMenipis = Produk::where('umkm_id', $umkm->id)
            ->where('stok', '<', $batasProduk)
            ->orderBy('stok')
            ->limit(6)
            ->get();

        // =========================
        // LIST: TRANSAKSI TERAKHIR
        // =========================
        $penjualanTerakhir = Penjualan::where('umkm_id', $umkm->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        // =========================
        // (OPSIONAL) GRAFIK: PRODUKSI 7 HARI (kalau tabelnya ada)
        // Kalau tabelmu beda nama, skip aja bagian ini.
        // =========================
        $produksiLabels7 = $labels7;
        $produksiData7 = array_fill(0, 7, 0);

        // Contoh asumsi tabel: produksi(tanggal, umkm_id) & produksi_detail(qty_hasil)
        // Kalau kamu belum yakin, amanin pake try-catch
        try {
            $prod = DB::table('produksi as pr')
                ->join('produksi_detail as pd', 'pd.produksi_id', '=', 'pr.id')
                ->where('pr.umkm_id', $umkm->id)
                ->whereBetween('pr.tanggal', [$start7, $today])
                ->selectRaw('DATE(pr.tanggal) as tgl, SUM(pd.qty_hasil) as total')
                ->groupBy('tgl')
                ->orderBy('tgl')
                ->pluck('total', 'tgl')
                ->toArray();

            $produksiData7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $produksiData7[] = (float) ($prod[$d] ?? 0);
            }
        } catch (\Throwable $e) {
            // kalau tabel tidak ada / namanya beda, biarin 0 semua
        }

        // =========================
        // CEK IURAN BULAN INI
        // =========================
        $iuranBulanIni = app(\App\Services\IuranService::class)->getOrCreate($umkm->id);
        $iuranBelumLunas = ($iuranBulanIni && $iuranBulanIni->status !== 'lunas') ? $iuranBulanIni : null;

        return view('umkm.dashboard.index', compact(
            'penjualanHariIni',
            'trxHariIni',
            'penjualanBulanIni',
            'totalProduk',
            'totalStokProduk',
            'labels7',
            'data7',
            'topLabels',
            'topData',
            'bahanMenipis',
            'produkMenipis',
            'penjualanTerakhir',
            'produksiLabels7',
            'produksiData7',
            'iuranBelumLunas'
        ));
    }
}
