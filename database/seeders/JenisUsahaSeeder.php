<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Jenis usaha dan rumpun bawaan — Tabel 3.7 (Juknis TKM Pemula).
 * Aman dijalankan ulang (updateOrInsert per nama).
 *
 * Jalankan: php artisan db:seed --class=JenisUsahaSeeder
 */
class JenisUsahaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Pertanian', 'barang'],
            ['Peternakan', 'barang'],
            ['Perikanan dan kelautan', 'barang'],
            ['Jasa boga (mis. usaha kuliner, warung kopi)', 'barang'],
            ['Usaha kreatif (mis. sablon, kerajinan tangan)', 'ditentukan_umkm'],
            ['Perdagangan barang (mis. toko kelontong, sembako)', 'barang'],
            ['Jasa perorangan (mis. kesehatan, servis elektronik, bengkel, pangkas rambut)', 'jasa'],
        ];

        $now = now();

        foreach ($data as [$nama, $rumpun]) {
            DB::table('jenis_usaha')->updateOrInsert(
                ['nama' => $nama],
                ['rumpun_bawaan' => $rumpun, 'aktif' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
