<?php

namespace App\Http\Controllers\Umkm\Concerns;

use Illuminate\Http\Request;

/** Periode laporan bulanan dari parameter `bulan` (Y-m, bawaan bulan berjalan). */
trait MembacaPeriode
{
    /** @return array{0:string,1:string,2:string} [tanggal awal, tanggal akhir, bulan] */
    private function getPeriode(Request $request): array
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);
        $awal  = "$year-$month-01";
        $akhir = date('Y-m-t', strtotime($awal));

        return [$awal, $akhir, $bulan];
    }
}
