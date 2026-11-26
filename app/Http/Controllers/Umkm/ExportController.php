<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\MembacaPeriode;
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
    use MembacaPeriode;

    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
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

    // 3 & 4. Laba Rugi dan Perubahan Modal: lihat ExportLabaModalController.

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
        $fullLedger = $ledgerResult['ledger'];
        $activeBatchesDb = $ledgerResult['activeBatches'];
        $methodStr = strtoupper($umkm->inventory_method ?? 'AVERAGE');

        $saldoAwalQty     = 0;
        $saldoAwalNilai   = 0;
        $saldoAwalBatches = [];
        $filteredLedger   = [];

        foreach ($fullLedger as $row) {
            $tglStr = substr($row['tanggal'], 0, 10);
            if ($row['jenis'] !== 'SALDO AWAL' && $tglStr < $awal) {
                // Catat transaksi terakhir sblm bulan mulai. (Jika Saldo Awal, otomatis akan kerangkum di sini juga jika tglnya lbh kecil)
                $saldoAwalQty     = $row['saldo_qty'];
                $saldoAwalNilai   = $row['saldo_nilai'];
                $saldoAwalBatches = $row['active_batches_snapshot'] ?? [];
            } elseif ($row['jenis'] === 'SALDO AWAL' || ($tglStr >= $awal && $tglStr <= $akhir)) {
                if ($row['jenis'] === 'SALDO AWAL') {
                    $saldoAwalQty     = $row['saldo_qty'];
                    $saldoAwalNilai   = $row['saldo_nilai'];
                    $saldoAwalBatches = $row['active_batches_snapshot'] ?? [];
                } else {
                    $filteredLedger[] = $row;
                }
            }
        }

        $format = $request->get('format', 'pdf');
        $title = "Kartu Stok: " . $bahan->nama_bahan . " - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Kartu_Stok_" . str_replace(' ', '_', $bahan->nama_bahan) . "_" . $bulan;

        if ($format === 'excel') {
            $headers = ['Tanggal', 'Transaksi', 'Msk Qty', 'Msk Harga', 'Msk Total', 'Klr Qty', 'Klr Harga', 'Klr Total', 'Sld Qty', 'Sld Harga', 'Sld Total'];
            $data = [];

            // INJECT SALDO AWAL
            if ($methodStr !== 'AVERAGE' && count($saldoAwalBatches) > 0) {
                foreach ($saldoAwalBatches as $idx => $batch) {
                    $data[] = [
                        $idx === 0 ? 'SALDO AWAL' : '',
                        $idx === 0 ? "Per 1 " . Carbon::parse($awal)->translatedFormat('M Y') : '',
                        '', '', '', '', '', '',
                        $batch['qty'],
                        $batch['harga'],
                        $batch['qty'] * $batch['harga']
                    ];
                }
            } else {
                $data[] = [
                    'SALDO AWAL', "Per 1 " . Carbon::parse($awal)->translatedFormat('M Y'),
                    '', '', '', '', '', '',
                    $saldoAwalQty,
                    $saldoAwalQty > 0 ? ($saldoAwalNilai / $saldoAwalQty) : 0,
                    $saldoAwalNilai
                ];
            }

            $sumMasukQty = 0; $sumMasukNilai = 0;
            $sumKeluarQty = 0; $sumKeluarNilai = 0;
            $finalSaldoQty = $saldoAwalQty; $finalSaldoNilai = $saldoAwalNilai;

            foreach ($filteredLedger as $row) {
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                $saldoHarga = $row['saldo_qty'] > 0 ? ($row['saldo_nilai'] / $row['saldo_qty']) : 0;

                $keluarNilaiTotalRun = 0;
                if (!$isMasuk) {
                    if (count($keluarDetails) > 0) {
                        foreach($keluarDetails as $kDet) { $keluarNilaiTotalRun += ($kDet['qty'] * $kDet['harga']); }
                    } else {
                        $keluarNilaiTotalRun = $keluarQtyTotal * $saldoHarga;
                    }
                }

                $sumMasukQty += $isMasuk ? $row['masuk_qty'] : 0;
                $sumMasukNilai += $masukNilai;
                $sumKeluarQty += $keluarQtyTotal;
                $sumKeluarNilai += $keluarNilaiTotalRun;
                $finalSaldoQty = $row['saldo_qty'];
                $finalSaldoNilai = $row['saldo_nilai'];

                $inOutLayers = [];
                if ($isMasuk) {
                    $inOutLayers[] = [
                        'jenis' => 'MASUK', 'qty' => $row['masuk_qty'], 'harga' => $row['masuk_harga'], 'nilai' => $masukNilai, 'info' => ''
                    ];
                } else {
                    if (count($keluarDetails) > 0) {
                        foreach($keluarDetails as $kd) {
                            $inOutLayers[] = ['jenis' => 'KELUAR', 'qty' => $kd['qty'], 'harga' => $kd['harga'], 'nilai' => $kd['qty'] * $kd['harga'], 'info' => $kd['batch'] ?? ''];
                        }
                    } else {
                        $inOutLayers[] = ['jenis' => 'KELUAR', 'qty' => $keluarQtyTotal, 'harga' => $saldoHarga, 'nilai' => $keluarQtyTotal * $saldoHarga, 'info' => ''];
                    }
                }

                $saldoLayers = [];
                if (isset($row['active_batches_snapshot']) && count($row['active_batches_snapshot']) > 0 && $methodStr !== 'AVERAGE') {
                    foreach($row['active_batches_snapshot'] as $b) {
                        $saldoLayers[] = ['qty' => $b['qty'], 'harga' => $b['harga'], 'nilai' => $b['qty'] * $b['harga']];
                    }
                } else {
                    $saldoLayers[] = ['qty' => $row['saldo_qty'], 'harga' => $saldoHarga, 'nilai' => $row['saldo_nilai']];
                }

                $maxRows = max(count($inOutLayers), count($saldoLayers));
                
                for ($i = 0; $i < $maxRows; $i++) {
                    $inOut = $inOutLayers[$i] ?? null;
                    $sdo   = $saldoLayers[$i] ?? null;

                    $colTgl = $i === 0 ? ($row['tanggal'] ? Carbon::parse($row['tanggal'])->format('d/m/Y') : '-') : '';
                    $colTrans = $i === 0 ? ($row['jenis'] . ' ' . $row['ref_tipe'] . ($row['ref_id'] ? ' #'.$row['ref_id'] : '')) : '';

                    $mQty = ''; $mHrg = ''; $mNil = '';
                    $kQty = ''; $kHrg = ''; $kNil = '';
                    
                    if ($inOut) {
                        if ($inOut['jenis'] === 'MASUK') {
                            $mQty = $inOut['qty']; $mHrg = $inOut['harga']; $mNil = $inOut['nilai'];
                        } else {
                            $kQty = $inOut['qty']; $kHrg = $inOut['harga']; $kNil = $inOut['nilai'];
                        }
                    }

                    $sQty = ''; $sHrg = ''; $sNil = '';
                    if ($sdo) {
                        $sQty = $sdo['qty']; $sHrg = $sdo['harga']; $sNil = $sdo['nilai'];
                    }

                    $data[] = [
                        $colTgl, $colTrans,
                        $mQty, $mHrg, $mNil,
                        $kQty, $kHrg, $kNil,
                        $sQty, $sHrg, $sNil
                    ];
                }
            }

            // INJECT TOTAL FOOTER
            if (!empty($filteredLedger) || $saldoAwalQty > 0) {
                $activeBatchesAtEnd = [];
                if (!empty($filteredLedger)) {
                    $activeBatchesAtEnd = end($filteredLedger)['active_batches_snapshot'] ?? [];
                } else {
                    $activeBatchesAtEnd = $saldoAwalBatches;
                }

                if ($methodStr !== 'AVERAGE' && count($activeBatchesAtEnd) > 0) {
                    foreach ($activeBatchesAtEnd as $idx => $batch) {
                        $data[] = [
                            $idx === 0 ? 'TOTAL PERGERAKAN' : '',
                            $idx === 0 ? '& SALDO AKHIR' : '',
                            $idx === 0 ? $sumMasukQty : '',
                            $idx === 0 ? '-' : '',
                            $idx === 0 ? $sumMasukNilai : '',
                            $idx === 0 ? $sumKeluarQty : '',
                            $idx === 0 ? '-' : '',
                            $idx === 0 ? $sumKeluarNilai : '',
                            $batch['qty'],
                            $batch['harga'],
                            $batch['qty'] * $batch['harga']
                        ];
                    }
                } else {
                    $data[] = [
                        'TOTAL PERGERAKAN', '& SALDO AKHIR',
                        $sumMasukQty,
                        '-',
                        $sumMasukNilai,
                        $sumKeluarQty,
                        '-',
                        $sumKeluarNilai,
                        $finalSaldoQty,
                        $finalSaldoQty > 0 ? ($finalSaldoNilai / $finalSaldoQty) : 0,
                        $finalSaldoNilai
                    ];
                }
            }

            return $this->exportService->toExcel($title, $headers, $data, $filename);
        }

        return $this->exportService->toPdf('exports.pdf.kartu_stok', compact('umkm', 'bahan', 'filteredLedger', 'awal', 'akhir', 'title', 'methodStr', 'saldoAwalQty', 'saldoAwalNilai', 'saldoAwalBatches', 'activeBatchesDb'), $filename, 'A4', 'landscape');
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
