<?php

namespace App\Services;

use App\Models\Indikator;
use App\Models\PilihanJawaban;
use App\Models\UmkmLevel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Menghitung skor kuesioner dan level rekomendasi.
 * Skor ada di tabel pilihan_jawaban dan batas level di umkm_level,
 * jadi mengubah data mengubah hasil tanpa mengubah kode (BR-09, BR-12).
 * Service ini hanya MEREKOMENDASIKAN; level ditetapkan Admin (BR-01).
 */
class PenilaianService
{
    /**
     * @param  array<int|string, int|string|null>  $jawaban  indikator_id => pilihan_id
     * @return array{total:int, level:UmkmLevel, perlu_keputusan_admin:bool, detail:array<int,array<string,mixed>>}
     *
     * @throws InvalidArgumentException jika ada pertanyaan belum dijawab / pilihan tidak valid
     * @throws RuntimeException jika tidak ada level yang cocok dengan total skor
     */
    public function hitung(array $jawaban): array
    {
        $total  = 0;
        $perlu  = false;
        $detail = [];

        foreach (Indikator::aktif()->orderBy('urutan')->get() as $indikator) {
            $pilihanId = $jawaban[$indikator->id] ?? null;

            $pilihan = $pilihanId
                ? PilihanJawaban::where('id', $pilihanId)->where('indikator_id', $indikator->id)->first()
                : null;

            if (!$pilihan) {
                throw new InvalidArgumentException(
                    "Pertanyaan {$indikator->urutan} belum dijawab dengan benar."
                );
            }

            $total += $pilihan->skor;
            $perlu = $perlu || $pilihan->di_luar_batas_mikro; // BR-11

            $detail[] = [
                'indikator_id' => $indikator->id,
                'pilihan_id'   => $pilihan->id,
                'teks_pilihan' => $pilihan->teks,
                'skor'         => $pilihan->skor,
            ];
        }

        $level = UmkmLevel::where('skor_min', '<=', $total)
            ->where('skor_max', '>=', $total)
            ->first();

        if (!$level) {
            throw new RuntimeException("Batas skor level belum diatur untuk total skor {$total}.");
        }

        return [
            'total'                 => $total,
            'level'                 => $level,
            'perlu_keputusan_admin' => $perlu,
            'detail'                => $detail,
        ];
    }
}
