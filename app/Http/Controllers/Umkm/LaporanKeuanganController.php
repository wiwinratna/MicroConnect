<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\StokMutasi;
use App\Models\Piutang;
use App\Models\PiutangPembayaran;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $umkm  = auth()->user()->umkm;
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $awal  = "$year-$month-01";
        $akhir = date('Y-m-t', strtotime($awal));

        // ================================================================
        // JURNAL bulan ini
        // ================================================================
        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        // ================================================================
        // BUKU BESAR — dikelompokkan per kode_akun
        // ================================================================
        $bukuBesar = $jurnal->groupBy('kode_akun')->map(function ($items) use ($umkm) {
            $kode       = $items->first()->kode_akun;
            $nama       = $items->first()->nama_akun;
            $totalDebit = $items->sum('debit');
            $totalKredit= $items->sum('kredit');
            $posisiDrCr = Coa::where('umkm_id', $umkm->id)
                ->where('kode_akun', $kode)->value('posisi_dr_cr') ?? 'Debit';
            $saldo = $posisiDrCr === 'Debit'
                ? ($totalDebit - $totalKredit)
                : ($totalKredit - $totalDebit);

            return [
                'nama_akun'    => $nama,
                'items'        => $items,
                'total_debit'  => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir'  => $saldo,
                'posisi'       => $posisiDrCr,
            ];
        });

        // ================================================================
        // LABA RUGI
        // ================================================================
        // Pendapatan (4xx)
        $pendapatan     = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '4'));
        $totalPendapatan= $pendapatan->sum('kredit') - $pendapatan->sum('debit');

        // HPP (5xx)
        $hpp     = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '5'));
        $totalHpp= $hpp->sum('debit') - $hpp->sum('kredit');

        $isPeriodik = strtoupper($umkm->recording_method ?? 'PERIODIK') === 'PERIODIK';
        $persediaanAwal = 0;
        $persediaanAkhir = 0;
        $totalPembelianBulanIni = 0;

        if ($isPeriodik) {
            $invService = new \App\Services\InventoryService();
            $bahanList = \App\Models\BahanBaku::where('umkm_id', $umkm->id)->get();
            
            $dateAwalMinus1 = date('Y-m-d', strtotime($awal . ' -1 day'));
            
            foreach ($bahanList as $b) {
                $awalResult = $invService->buildLedger($umkm, 'bahan', $b->id, $dateAwalMinus1);
                $persediaanAwal += $awalResult['totalSaldoNilai'];
                
                $akhirResult = $invService->buildLedger($umkm, 'bahan', $b->id, $akhir);
                $persediaanAkhir += $akhirResult['totalSaldoNilai'];
            }
            
            $totalPembelianBulanIni = $hpp->filter(fn($j) => $j->kode_akun === '510')->sum(fn($j) => $j->debit - $j->kredit);
            $totalHpp = $persediaanAwal + $totalPembelianBulanIni - $persediaanAkhir;
        }

        $labaKotor = $totalPendapatan - $totalHpp;

        // Beban Operasional (6xx)
        $beban      = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '6'));
        $totalBeban = $beban->sum('debit') - $beban->sum('kredit');
        // Breakdown beban per kategori
        $bebanDetail= $beban->groupBy('kode_akun')->map(fn($g) => [
            'nama'  => $g->first()->nama_akun,
            'total' => $g->sum('debit') - $g->sum('kredit'),
        ]);
        $labaBersih = $labaKotor - $totalBeban;

        // ================================================================
        // PERUBAHAN MODAL
        // ================================================================
        $modalAwal = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', 'like', '3%')
            ->where('tanggal', '<', $awal)
            ->sum(DB::raw('kredit - debit')) ?: 0;

        $prive      = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '32'))
            ->sum(fn($j) => $j->debit - $j->kredit);
        $modalAkhir = $modalAwal + $labaBersih - $prive;

        // ================================================================
        // ARUS KAS SEDERHANA (dari jurnal akun 111)
        // ================================================================
        $kasJurnal       = $jurnal->filter(fn($j) => $j->kode_akun === '111');
        $kasIn           = $kasJurnal->sum('debit');
        $kasOut          = $kasJurnal->sum('kredit');
        $netKas          = $kasIn - $kasOut;
        $kasAwalPeriode  = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', '111')
            ->where('tanggal', '<', $awal)
            ->sum(DB::raw('debit - kredit')) ?: 0;
        $kasAkhirPeriode = $kasAwalPeriode + $netKas;

        // ================================================================
        // LAPORAN PEMBELIAN (detail)
        // ================================================================
        $pembelianList = Pembelian::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->with('details.bahan')
            ->orderBy('tanggal')
            ->get();
        $totalPembelian = $pembelianList->sum('total');

        // ================================================================
        // LAPORAN PENJUALAN (detail)
        // ================================================================
        $penjualanList = Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->with('details.produk')
            ->orderBy('tanggal')
            ->get();
        $totalPenjualan = $penjualanList->sum('total');

        // ================================================================
        // LAPORAN PERSEDIAAN / STOK BAHAN
        // ================================================================
        $mutasiStok = StokMutasi::where('umkm_id', $umkm->id)
            ->whereNotNull('bahan_id')
            ->whereBetween('tanggal', [$awal, $akhir])
            ->with('bahan')
            ->orderBy('tanggal')
            ->get();

        $stokPerBahan = $mutasiStok->groupBy('bahan_id')->map(function ($items) {
            $bahan   = $items->first()->bahan;
            $masuk   = $items->where('jenis', 'MASUK')->sum('qty');
            $keluar  = $items->where('jenis', 'KELUAR')->sum('qty');
            $nilaiMasuk = $items->where('jenis', 'MASUK')->sum(fn($m) => $m->qty * (float)$m->harga_unit);
            return [
                'nama_bahan'  => $bahan?->nama_bahan ?? 'N/A',
                'satuan'      => $bahan?->satuan ?? '',
                'masuk'       => $masuk,
                'keluar'      => $keluar,
                'saldo'       => $masuk - $keluar,
                'nilai_masuk' => $nilaiMasuk,
            ];
        });

        // ================================================================
        // LAPORAN PIUTANG & PEMBAYARAN
        // ================================================================
        $piutangList = Piutang::where('umkm_id', $umkm->id)
            ->with(['pelanggan', 'pembayaran'])
            ->orderBy('tanggal')
            ->get();

        $totalPiutang         = $piutangList->sum('nominal_awal');
        $totalSudahDibayar    = $piutangList->sum('sudah_dibayar');
        $totalSisaPiutang     = $piutangList->sum('sisa');

        $pembayaranPiutang = PiutangPembayaran::whereHas('piutang', fn($q) => $q->where('umkm_id', $umkm->id))
            ->whereBetween('tanggal_bayar', [$awal, $akhir])
            ->with('piutang.pelanggan')
            ->orderBy('tanggal_bayar')
            ->get();
        $totalPembayaranBulanIni = $pembayaranPiutang->sum('jumlah_bayar');

        // ================================================================
        // MUTASI KAS (shortcut dari buku besar)
        // ================================================================
        $mutasiKas    = $bukuBesar->get('111');
        $mutasiPiutang= $bukuBesar->get('112');

        return view('umkm.laporan.index', compact(
            // meta
            'bulan', 'awal', 'akhir',
            // jurnal & buku besar
            'jurnal', 'bukuBesar',
            // laba rugi
            'pendapatan', 'totalPendapatan',
            'hpp', 'totalHpp', 'labaKotor',
            'isPeriodik', 'persediaanAwal', 'persediaanAkhir', 'totalPembelianBulanIni',
            'beban', 'bebanDetail', 'totalBeban',
            'labaBersih',
            // modal
            'modalAwal', 'prive', 'modalAkhir',
            // kas
            'kasIn', 'kasOut', 'netKas', 'kasAwalPeriode', 'kasAkhirPeriode', 'mutasiKas',
            // pembelian
            'pembelianList', 'totalPembelian',
            // penjualan
            'penjualanList', 'totalPenjualan',
            // stok
            'stokPerBahan',
            // piutang
            'piutangList', 'totalPiutang', 'totalSudahDibayar', 'totalSisaPiutang',
            'pembayaranPiutang', 'totalPembayaranBulanIni',
            'mutasiPiutang'
        ));
    }
}
