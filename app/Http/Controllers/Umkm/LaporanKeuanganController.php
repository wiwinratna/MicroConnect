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
        $umkm = auth()->user()->umkm;
        return $this->buildLaporan($request, $umkm);
    }

    public function adminView(Request $request, $id)
    {
        if (auth()->user()->user_group !== 'admin') abort(403);
        $umkm = \App\Models\Umkm::findOrFail($id);
        return $this->buildLaporan($request, $umkm);
    }

    private function buildLaporan(Request $request, $umkm)
    {
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
        // LAPORAN ARUS KAS (dari jurnal akun 111)
        // ================================================================
        $kasJurnal       = $jurnal->filter(fn($j) => $j->kode_akun === '111')->values();
        $kasIn           = $kasJurnal->sum('debit');
        $kasOut          = $kasJurnal->sum('kredit');
        $netKas          = $kasIn - $kasOut;
        $kasAwalPeriode  = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', '111')
            ->where('tanggal', '<', $awal)
            ->sum(DB::raw('debit - kredit')) ?: 0;
        $kasAkhirPeriode = $kasAwalPeriode + $netKas;

        // Detail Arus Kas (Pengelompokan Otomatis)
        $arusKasGrup = [
            'Aktivitas Operasional' => ['masuk' => [], 'keluar' => [], 'total_masuk' => 0, 'total_keluar' => 0],
            'Aktivitas Investasi & Pendanaan' => ['masuk' => [], 'keluar' => [], 'total_masuk' => 0, 'total_keluar' => 0],
        ];

        foreach ($kasJurnal as $kas) {
            $isMasuk = $kas->debit > 0;
            $nominal = $isMasuk ? $kas->debit : $kas->kredit;
            $tipe = $kas->ref_tipe;
            
            $sumber = 'Transaksi Kas Lainnya';
            $grup = 'Aktivitas Operasional';

            if ($tipe === 'penjualan') {
                $sumber = 'Penerimaan Penjualan';
            } elseif ($tipe === 'pembayaran_piutang') {
                $sumber = 'Penerimaan Pelunasan Piutang';
            } elseif ($tipe === 'pembelian') {
                $sumber = 'Pembayaran Pembelian Bahan/Barang';
            } elseif ($tipe === 'beban') {
                // Cari akun lawannya untuk tahu beban apa (karena beban tdk pakai ref_id)
                $lawan = $jurnal->where('tanggal', $kas->tanggal)
                                ->where('ref_tipe', 'beban')
                                ->where('keterangan', $kas->keterangan)
                                ->where('id', '!=', $kas->id)
                                ->first();
                $sumber = $lawan ? $lawan->nama_akun : 'Pembayaran Beban Operasional';
            } else {
                // Jurnal manual, cari lawannya di date & ket yg sama
                $lawan = $jurnal->where('tanggal', $kas->tanggal)
                                ->where('keterangan', $kas->keterangan)
                                ->where('id', '!=', $kas->id)
                                ->first();
                if ($lawan) {
                    $sumber = $lawan->nama_akun;
                    // Tentukan grup dari kode_akun lawan
                    $awalan = substr($lawan->kode_akun, 0, 1);
                    if (in_array($awalan, ['2', '3'])) {
                        $grup = 'Aktivitas Investasi & Pendanaan'; // Hutang / Modal
                    } elseif ($awalan == '1' && !in_array($lawan->kode_akun, ['111','112','113','114','115','116'])) {
                        // Aset tetap (Peralatan dll) -> Investasi
                        $grup = 'Aktivitas Investasi & Pendanaan';
                    }
                }
            }

            // Masukkan ke grup
            if ($isMasuk) {
                if (!isset($arusKasGrup[$grup]['masuk'][$sumber])) $arusKasGrup[$grup]['masuk'][$sumber] = 0;
                $arusKasGrup[$grup]['masuk'][$sumber] += $nominal;
                $arusKasGrup[$grup]['total_masuk'] += $nominal;
            } else {
                if (!isset($arusKasGrup[$grup]['keluar'][$sumber])) $arusKasGrup[$grup]['keluar'][$sumber] = 0;
                $arusKasGrup[$grup]['keluar'][$sumber] += $nominal;
                $arusKasGrup[$grup]['total_keluar'] += $nominal;
            }
        }

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
            'arusKasGrup',
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
