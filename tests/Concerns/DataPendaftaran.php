<?php

namespace Tests\Concerns;

use App\Models\Indikator;
use App\Models\JenisUsaha;
use Database\Seeders\IndikatorSeeder;
use Database\Seeders\JenisUsahaSeeder;
use Database\Seeders\ModulSeeder;
use Database\Seeders\UmkmLevelSeeder;

/** Pembantu tes K1: data awal kuesioner dan payload formulir pendaftaran. */
trait DataPendaftaran
{
    protected function seedK1(): void
    {
        $this->seed(UmkmLevelSeeder::class);
        $this->seed(JenisUsahaSeeder::class);
        $this->seed(ModulSeeder::class);
        $this->seed(IndikatorSeeder::class);
    }

    /** 'abca' => [indikator_id => pilihan_id] (huruf a = pilihan ke-1, dst). */
    protected function jawab(string $huruf): array
    {
        $jawaban = [];

        foreach (str_split($huruf) as $i => $h) {
            $indikator = Indikator::where('urutan', $i + 1)->firstOrFail();
            $pilihan   = $indikator->pilihan()->where('urutan', ord($h) - 96)->firstOrFail();
            $jawaban[$indikator->id] = $pilihan->id;
        }

        return $jawaban;
    }

    protected function jenisId(string $awalan): int
    {
        return JenisUsaha::where('nama', 'like', $awalan . '%')->firstOrFail()->id;
    }

    protected function payload(array $ubah = [], string $huruf = 'aaaa'): array
    {
        return array_merge([
            'nama_pemilik'             => 'Budi Santoso',
            'email'                    => 'budi.baru@example.test',
            'username'                 => 'budi_baru',
            'no_wa'                    => '081234567890',
            'nama_usaha'               => 'Warung Budi',
            'alamat'                   => 'Jl. Merdeka No. 1, Bengkalis',
            'no_telepon'               => '',
            'jenis_usaha_id'           => $this->jenisId('Pertanian'),
            'mengolah_bahan'           => '1',
            'jawaban'                  => $this->jawab($huruf),
            'persetujuan_data_pribadi' => '1',
        ], $ubah);
    }
}
