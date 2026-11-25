<?php

namespace App\Services;

use App\Models\Indikator;
use App\Models\PilihanJawaban;
use App\Models\Umkm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Angka dari pembukuan sistem untuk pengisian bawaan penilaian ulang (omzet dan modal).
 *
 *  - Omzet : total pendapatan (akun 4xx, sama dengan Laba Rugi) 12 bulan terakhir.
 *  - Modal : modal akhir per hari penilaian = saldo akun modal 3xx (sudah termasuk prive)
 *            + laba bersih kumulatif (rumus yang sama dengan laporan Perubahan Modal).
 *  - Data yang tersedia kurang dari 12 bulan dipakai apa adanya (tidak diproyeksikan);
 *    jumlah hari data ikut dikembalikan agar bisa ditampilkan.
 *
 * Angka ini hanya MENGISI BAWAAN pilihan; skor tetap dari pilihan yang dipilih UMKM (BR-12).
 * Rentang nilai_min/nilai_max di pilihan_jawaban dipakai untuk mencocokkan angka ke pilihan.
 */
class DataSistemService
{
    /** @return array{hari_data:int, omzet:?float, modal:?float, sampai:string} */
    public function hitung(Umkm $umkm, ?Carbon $hariIni = null): array
    {
        $hariIni = ($hariIni ?? now())->copy()->startOfDay();
        $sampai  = $hariIni->toDateString();
        $mulai   = $hariIni->copy()->subYear()->addDay()->toDateString();

        $jurnal  = fn () => DB::table('jurnal_umum')->where('umkm_id', $umkm->id);
        $pertama = $jurnal()->min('tanggal');

        if (!$pertama) {
            return ['hari_data' => 0, 'omzet' => null, 'modal' => null, 'sampai' => $sampai];
        }

        $hariData = min(365, (int) floor(Carbon::parse($pertama)->startOfDay()->diffInDays($hariIni)) + 1);

        $omzet = (float) $jurnal()->where('kode_akun', 'like', '4%')
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->sum(DB::raw('kredit - debit'));

        $sampaiHariIni = fn (string $awalanAkun) => $jurnal()
            ->where('kode_akun', 'like', $awalanAkun . '%')
            ->where('tanggal', '<=', $sampai);

        $saldoModal = (float) $sampaiHariIni('3')->sum(DB::raw('kredit - debit'));
        $pendapatan = (float) $sampaiHariIni('4')->sum(DB::raw('kredit - debit'));
        $hpp        = (float) $sampaiHariIni('5')->sum(DB::raw('debit - kredit'));
        $beban      = (float) $sampaiHariIni('6')->sum(DB::raw('debit - kredit'));

        return [
            'hari_data' => $hariData,
            'omzet'     => $omzet,
            'modal'     => $saldoModal + $pendapatan - $hpp - $beban,
            'sampai'    => $sampai,
        ];
    }

    /** 'omzet', 'modal', atau null: indikator mana yang bisa diisi dari data sistem. */
    public function jenisSistem(Indikator $indikator): ?string
    {
        $nama = strtolower($indikator->nama);

        return match (true) {
            str_starts_with($nama, 'omzet') => 'omzet',
            str_starts_with($nama, 'modal') => 'modal',
            default                         => null,
        };
    }

    /**
     * Pilihan yang rentangnya memuat angka: lebih dari nilai_min (bila ada) dan sampai
     * dengan nilai_max (bila ada), mengikuti redaksi Tabel 3.6.
     */
    public function pilihanUntuk(Indikator $indikator, float $nilai): ?PilihanJawaban
    {
        return $indikator->pilihan->first(function (PilihanJawaban $p) use ($nilai) {
            $atasMin = $p->nilai_min === null || $nilai > (float) $p->nilai_min;
            $bawahMax = $p->nilai_max === null || $nilai <= (float) $p->nilai_max;

            return $atasMin && $bawahMax;
        });
    }
}
