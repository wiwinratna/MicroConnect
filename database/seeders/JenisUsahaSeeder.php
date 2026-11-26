<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data awal kategori usaha dan jenisnya — Tabel 3.7 (Juknis TKM Pemula).
 * Aman dijalankan ulang (nama yang sudah ada dilewati).
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

        // Hanya menambah yang belum ada, agar perubahan Admin tidak tertimpa.
        foreach ($data as [$nama, $rumpun]) {
            if (DB::table('jenis_usaha')->where('nama', $nama)->exists()) {
                continue;
            }

            DB::table('jenis_usaha')->insert([
                'nama' => $nama, 'rumpun_bawaan' => $rumpun, 'aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
