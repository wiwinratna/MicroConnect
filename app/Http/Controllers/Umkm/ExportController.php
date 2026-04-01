<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\StokMutasi;
use App\Models\Piutang;
use App\Models\Coa;
use App\Models\BahanBaku;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    private function getPeriode(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);
        $awal  = "$year-$month-01";
        $akhir = date('Y-m-t', strtotime($awal));
        return [$awal, $akhir, $bulan];
    }

    // ==========================================
    // 1. JURNAL UMUM (Excel & PDF)
    // ==========================================
    public function jurnalUmum(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $format = $request->get('format', 'pdf');
        $title = "Jurnal Umum - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Jurnal_Umum_" . $umkm->nama_usaha . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Tanggal', 'No. Referensi', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Debit', 'Kredit'];
            $data = $jurnal->map(function ($j) {
                return [
                    $j->tanggal,
                    $j->ref_tipe,
                    $j->kode_akun,
                    $j->nama_akun,
                    $j->keterangan,
                    $j->debit,
                    $j->kredit
                ];
            })->toArray();
            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.jurnal_umum', compact('umkm', 'jurnal', 'awal', 'akhir', 'title'), $filename);
    }

    // ==========================================
    // 2. BUKU BESAR (PDF Only)
    // ==========================================
    public function bukuBesar(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $bukuBesar = $jurnal->groupBy('kode_akun')->map(function ($items) use ($umkm) {
            $kode       = $items->first()->kode_akun;
            $nama       = $items->first()->nama_akun;
            $totalDebit = $items->sum('debit');
            $totalKredit= $items->sum('kredit');
            $posisiDrCr = Coa::where('umkm_id', $umkm->id)->where('kode_akun', $kode)->value('posisi_dr_cr') ?? 'Debit';
            $saldo = $posisiDrCr === 'Debit' ? ($totalDebit - $totalKredit) : ($totalKredit - $totalDebit);

            return [
                'nama_akun'    => $nama,
                'items'        => $items,
                'total_debit'  => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir'  => $saldo,
                'posisi'       => $posisiDrCr,
            ];
        });

        $title = "Buku Besar - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Buku_Besar_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.buku_besar', compact('umkm', 'bukuBesar', 'awal', 'akhir', 'title'), $filename);
    }

    // ==========================================
    // 3. LABA RUGI (PDF Only)
    // ==========================================
    public function labaRugi(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->get();

        $pendapatan = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '4'));
        $totalPendapatan = $pendapatan->sum('kredit') - $pendapatan->sum('debit');

        $hpp = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '5'));
        $totalHpp = $hpp->sum('debit') - $hpp->sum('kredit');

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

        $beban = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '6'));
        $totalBeban = $beban->sum('debit') - $beban->sum('kredit');
        $bebanDetail= $beban->groupBy('kode_akun')->map(fn($g) => [
            'nama'  => $g->first()->nama_akun,
            'total' => $g->sum('debit') - $g->sum('kredit'),
        ]);

        $labaBersih = $labaKotor - $totalBeban;

        $title = "Laporan Laba Rugi - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Laba_Rugi_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.laba_rugi', compact(
            'umkm', 'pendapatan', 'totalPendapatan', 'hpp', 'totalHpp', 'labaKotor',
            'bebanDetail', 'totalBeban', 'labaBersih', 'isPeriodik', 'persediaanAwal', 'persediaanAkhir', 'totalPembelianBulanIni',
            'awal', 'akhir', 'title'
        ), $filename);
    }

    // ==========================================
    // 4. PERUBAHAN MODAL (PDF Only)
    // ==========================================
    public function perubahanModal(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->get();

        $modalAwal = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', 'like', '3%')
            ->where('tanggal', '<', $awal)
            ->sum(DB::raw('kredit - debit')) ?: 0;

        $pendapatan = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '4'))->sum(fn($j) => $j->kredit - $j->debit);
        $hpp = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '5'))->sum(fn($j) => $j->debit - $j->kredit);
        
        $isPeriodik = strtoupper($umkm->recording_method ?? 'PERIODIK') === 'PERIODIK';
        if ($isPeriodik) {
            $invService = new \App\Services\InventoryService();
            $bahanList = \App\Models\BahanBaku::where('umkm_id', $umkm->id)->get();
            $persediaanAwal = 0; $persediaanAkhir = 0;
            $dateAwalMinus1 = date('Y-m-d', strtotime($awal . ' -1 day'));
            foreach ($bahanList as $b) {
                $awalResult = $invService->buildLedger($umkm, 'bahan', $b->id, $dateAwalMinus1);
                $persediaanAwal += $awalResult['totalSaldoNilai'];
                $akhirResult = $invService->buildLedger($umkm, 'bahan', $b->id, $akhir);
                $persediaanAkhir += $akhirResult['totalSaldoNilai'];
            }
            $totalPembelianBulanIni = $jurnal->filter(fn($j) => $j->kode_akun === '510')->sum(fn($j) => $j->debit - $j->kredit);
            $hpp = $persediaanAwal + $totalPembelianBulanIni - $persediaanAkhir;
        }

        $beban = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '6'))->sum(fn($j) => $j->debit - $j->kredit);
        $labaBersih = $pendapatan - $hpp - $beban;

        $prive = $jurnal->filter(fn($j) => str_starts_with($j->kode_akun, '32'))->sum(fn($j) => $j->debit - $j->kredit);
        $modalAkhir = $modalAwal + $labaBersih - $prive;

        $title = "Laporan Perubahan Modal - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Perubahan_Modal_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.perubahan_modal', compact(
            'umkm', 'modalAwal', 'labaBersih', 'prive', 'modalAkhir', 'awal', 'akhir', 'title'
        ), $filename);
    }

    // ==========================================
    // 5. ARUS KAS (PDF Only)
    // ==========================================
    public function arusKas(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->get();

        $kasJurnal = $jurnal->filter(fn($j) => $j->kode_akun === '111');
        $kasIn = $kasJurnal->sum('debit');
        $kasOut = $kasJurnal->sum('kredit');
        $netKas = $kasIn - $kasOut;
        
        $kasAwalPeriode = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', '111')
            ->where('tanggal', '<', $awal)
            ->sum(DB::raw('debit - kredit')) ?: 0;
            
        $kasAkhirPeriode = $kasAwalPeriode + $netKas;

        $title = "Laporan Arus Kas - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Arus_Kas_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.arus_kas', compact(
            'umkm', 'kasJurnal', 'kasIn', 'kasOut', 'netKas', 'kasAwalPeriode', 'kasAkhirPeriode', 'awal', 'akhir', 'title'
        ), $filename);
    }

    // ==========================================
    // 6. REKAP MUTASI STOK (Excel & PDF)
    // ==========================================
    public function rekapStok(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

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
            return (object) [
                'nama_bahan'  => $bahan?->nama_bahan ?? 'N/A',
                'satuan'      => $bahan?->satuan ?? '',
                'masuk'       => $masuk,
                'keluar'      => $keluar,
                'saldo'       => $masuk - $keluar,
                'nilai_masuk' => $nilaiMasuk,
            ];
        });

        $format = $request->get('format', 'pdf');
        $title = "Rekap Mutasi Stok - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Rekap_Stok_" . $umkm->nama_usaha . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Nama Bahan Baku', 'Satuan', 'Total Masuk', 'Total Keluar', 'Saldo Berjalan', 'Nilai Masuk (Rp)'];
            $data = $stokPerBahan->map(function ($s) {
                return [
                    $s->nama_bahan,
                    $s->satuan,
                    $s->masuk,
                    $s->keluar,
                    $s->saldo,
                    $s->nilai_masuk
                ];
            })->toArray();
            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.rekap_stok', compact('umkm', 'stokPerBahan', 'awal', 'akhir', 'title'), $filename);
    }

    // ==========================================
    // 7. KARTU STOK DETAIL (Excel & PDF)
    // ==========================================
    public function kartuStokDetail(Request $request)
    {
        $umkm = Auth::user()->umkm;
        
        $bahanId = $request->get('bahan_id');
        $bulan   = $request->get('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);
        $awal  = "$year-$month-01";
        $akhir = date('Y-m-t', strtotime($awal));
        $dateAkhirDb = $akhir . ' 23:59:59';

        $bahan = BahanBaku::where('umkm_id', $umkm->id)->findOrFail($bahanId);

        $invService = new \App\Services\InventoryService();
        $ledgerResult = $invService->buildLedger($umkm, 'bahan', $bahanId, $dateAkhirDb);
        
        // Filter ledger hanya untuk rentang bulan ini ATAU saldo awal
        $filteredLedger = array_filter($ledgerResult['ledger'], function($row) use ($awal, $akhir) {
            if ($row['jenis'] === 'SALDO AWAL') return true;
            $tgl = substr($row['tanggal'], 0, 10);
            return $tgl >= $awal && $tgl <= $akhir;
        });

        $format = $request->get('format', 'pdf');
        $title = "Kartu Stok: " . $bahan->nama_bahan . " - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Kartu_Stok_" . str_replace(' ', '_', $bahan->nama_bahan) . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Tanggal', 'Transaksi', 'Msk Qty', 'Msk Harga', 'Msk Total', 'Klr Qty', 'Klr Harga', 'Klr Total', 'Sld Qty', 'Sld Total'];
            $data = [];
            foreach ($filteredLedger as $row) {
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                
                $keluarHargaAvg = 0;
                $keluarNilaiTotal = 0;
                if (!$isMasuk && count($keluarDetails) > 0) {
                    foreach($keluarDetails as $det) {
                        $keluarNilaiTotal += ($det['qty'] * $det['harga']);
                    }
                    $keluarHargaAvg = $keluarQtyTotal > 0 ? ($keluarNilaiTotal / $keluarQtyTotal) : 0;
                }

                $data[] = [
                    $row['tanggal'] ? Carbon::parse($row['tanggal'])->format('d/m/Y') : '-',
                    $row['jenis'] . ($row['ref_tipe'] ? ' - ' . $row['ref_tipe'] : ''),
                    $isMasuk ? $row['masuk_qty'] : '',
                    $isMasuk ? $row['masuk_harga'] : '',
                    $isMasuk ? $masukNilai : '',
                    !$isMasuk ? $keluarQtyTotal : '',
                    !$isMasuk ? $keluarHargaAvg : '',
                    !$isMasuk ? $keluarNilaiTotal : '',
                    $row['saldo_qty'],
                    $row['saldo_nilai']
                ];
            }
            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.kartu_stok', compact('umkm', 'bahan', 'filteredLedger', 'awal', 'akhir', 'title'), $filename, 'A4', 'landscape');
    }

    // ==========================================
    // 8. LAPORAN PEMBELIAN (Excel & PDF)
    // ==========================================
    public function laporanPembelian(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $pembelian = Pembelian::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->with('details.bahan')
            ->orderBy('tanggal')
            ->get();

        $format = $request->get('format', 'pdf');
        $title = "Laporan Pembelian - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Lap_Pembelian_" . $umkm->nama_usaha . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Tanggal', 'No. Faktur', 'Supplier', 'Item Dibeli', 'Total (Rp)'];
            $data = $pembelian->map(function ($p) {
                $items = $p->details->map(fn($d) => ($d->bahan->nama_bahan ?? 'N/A') . ' (' . $d->qty . ')')->implode(', ');
                return [
                    $p->tanggal,
                    $p->kode_pembelian,
                    $p->supplier ?: 'Umum',
                    $items,
                    $p->total
                ];
            })->toArray();
            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.laporan_pembelian', compact('umkm', 'pembelian', 'awal', 'akhir', 'title'), $filename);
    }

    // ==========================================
    // 9. LAPORAN PENJUALAN (Excel & PDF)
    // ==========================================
    public function laporanPenjualan(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $penjualan = Penjualan::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->with('details.produk')
            ->orderBy('tanggal')
            ->get();

        $format = $request->get('format', 'pdf');
        $title = "Laporan Penjualan - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Lap_Penjualan_" . $umkm->nama_usaha . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Tanggal', 'No. Transaksi', 'Pelanggan/Pembeli', 'Item Terjual', 'Catatan', 'Total (Rp)'];
            $data = $penjualan->map(function ($p) {
                $items = $p->details->map(fn($d) => ($d->produk->nama_produk ?? 'N/A') . ' (' . $d->qty . ')')->implode(', ');
                return [
                    $p->tanggal,
                    $p->kode_penjualan,
                    $p->pembeli ?: 'Tunai / Umum',
                    $items,
                    $p->catatan,
                    $p->total
                ];
            })->toArray();
            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.laporan_penjualan', compact('umkm', 'penjualan', 'awal', 'akhir', 'title'), $filename);
    }

    // ==========================================
    // 10. LAPORAN PIUTANG (PDF Only)
    // ==========================================
    public function laporanPiutang(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $piutangList = Piutang::where('umkm_id', $umkm->id)
            ->with(['pelanggan', 'pembayaran'])
            ->orderBy('tanggal')
            ->get();

        $title = "Laporan Piutang Pelanggan - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Lap_Piutang_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.laporan_piutang', compact('umkm', 'piutangList', 'awal', 'akhir', 'title'), $filename);
    }
}
