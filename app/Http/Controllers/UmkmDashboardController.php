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
    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $daterange = $request->get('daterange');
        $startDateStr = now()->subDays(6)->toDateString();
        $endDateStr = now()->toDateString();
        
        if ($daterange && str_contains($daterange, ' to ')) {
            $parts = explode(' to ', $daterange);
            $startDateStr = trim($parts[0]);
            $endDateStr = trim($parts[1] ?? $startDateStr);
        } elseif ($daterange) {
            $startDateStr = $daterange;
            $endDateStr = $daterange;
        }
        
        $start = \Carbon\Carbon::parse($startDateStr)->startOfDay();
        $end = \Carbon\Carbon::parse($endDateStr)->endOfDay();
        
        // Pengecekan batas maksimum agar jika user jahil milih 1 bulan tidak error
        if ($start->diffInDays($end) > 31) {
            $start = $end->clone()->subDays(31)->startOfDay();
        }

        $tanggalMulai = $start->toDateString();
        $tanggalAkhir = $end->toDateString();
        
        $labelRentang = $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        if ($start->isSameDay($end)) {
            $labelRentang = $start->translatedFormat('d F Y');
        }

        // =========================
        // KPI CARDS
        // =========================
        $penjualanRentang = (float) Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$start, $end])
            ->sum('total');

        $trxRentang = (int) Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$start, $end])
            ->count();

        $totalBahanAktif = (int) BahanBaku::where('umkm_id', $umkm->id)->where('is_archived', false)->count();
        // =========================
        // GRAFIK: PENJUALAN RENTANG WAKTU
        // =========================
        $salesDataRaw = Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl')
            ->toArray();

        // Bikin label sesuai durasi dari start ke end
        $labelsGrafik = [];
        $dataGrafik = [];
        $currentDate = $start->clone();
        
        while ($currentDate->lte($end)) {
            $d = $currentDate->toDateString();
            $labelsGrafik[] = $currentDate->translatedFormat('d M'); // Biar muat & rapi
            $dataGrafik[] = (float) ($salesDataRaw[$d] ?? 0);
            $currentDate->addDay();
        }

        // =========================
        // TOP 5 PRODUK TERLARIS (Periode Terpilih)
        // =========================
        $topProduk = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('produk as pr', 'pr.id', '=', 'pd.produk_id')
            ->where('p.umkm_id', $umkm->id)
            ->whereBetween('p.tanggal', [$start, $end])
            ->selectRaw('pr.nama_produk as nama, SUM(pd.qty) as qty')
            ->groupBy('pr.nama_produk')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $topLabels = $topProduk->pluck('nama')->toArray();
        $topData = $topProduk->pluck('qty')->map(fn($v) => (float)$v)->toArray();

        // =========================
        // ALERT: STOK MENIPIS (Berdasarkan Kalkulasi Mutasi Real-Time)
        // =========================
        $batasBahan = 5;  // kamu bisa ganti

        $bahanMenipis = BahanBaku::where('umkm_id', $umkm->id)
            ->where('is_archived', false)
            ->select('bahan_baku.*', DB::raw('(COALESCE((SELECT SUM(qty) FROM stok_mutasi WHERE bahan_id = bahan_baku.id AND jenis = "MASUK"), 0) - COALESCE((SELECT SUM(qty) FROM stok_mutasi WHERE bahan_id = bahan_baku.id AND jenis = "KELUAR"), 0)) as current_stok'))
            ->having('current_stok', '<', $batasBahan)
            ->orderBy('current_stok')
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

        // (Logika produksi telah dihapus sesuai instruksi karena sistem ini tidak lagi memisahkan alur produksi)

        // =========================
        // CEK IURAN BULAN INI
        // =========================
        $iuranBulanIni = app(\App\Services\IuranService::class)->getOrCreate($umkm->id);
        $iuranBelumLunas = ($iuranBulanIni && $iuranBulanIni->status !== 'lunas') ? $iuranBulanIni : null;

        return view('umkm.dashboard.index', compact(
            'daterange',
            'labelRentang',
            'penjualanRentang',
            'trxRentang',
            'totalBahanAktif',
            'labelsGrafik',
            'dataGrafik',
            'topLabels',
            'topData',
            'bahanMenipis',
            'penjualanTerakhir',
            'iuranBelumLunas'
        ));
    }
}
