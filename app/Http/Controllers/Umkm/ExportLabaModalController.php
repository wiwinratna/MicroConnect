<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\MembacaPeriode;
use App\Services\ExportService;
use App\Services\LabaModalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Ekspor PDF Laporan Laba Rugi dan Perubahan Modal. */
class ExportLabaModalController extends Controller
{
    use MembacaPeriode;

    public function __construct(
        private ExportService $exportService,
        private LabaModalService $hitung,
    ) {
    }

    public function labaRugi(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $data = $this->hitung->labaRugi($umkm, $awal, $akhir);

        $title = "Laporan Laba Rugi - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Laba_Rugi_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.laba_rugi', $data + compact(
            'umkm', 'awal', 'akhir', 'title'
        ), $filename);
    }

    public function perubahanModal(Request $request)
    {
        $umkm = Auth::user()->umkm;
        [$awal, $akhir, $bulan] = $this->getPeriode($request);

        $data = $this->hitung->perubahanModal($umkm, $awal, $akhir);

        $title = "Laporan Perubahan Modal - " . Carbon::parse($awal)->translatedFormat('F Y');
        $filename = "Perubahan_Modal_" . $umkm->nama_usaha . "_" . $bulan;

        return $this->exportService->toPdf('exports.pdf.perubahan_modal', $data + compact(
            'umkm', 'awal', 'akhir', 'title'
        ), $filename);
    }
}
