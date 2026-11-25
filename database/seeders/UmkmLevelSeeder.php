<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Helpers\FeatureAccess;
use App\Models\UmkmLevel;
use Illuminate\Support\Facades\Schema;

/**
 * UmkmLevelSeeder
 *
 * Mengisi tabel umkm_level dengan 3 level kumulatif.
 * Kolom `fitur` di DB disinkronkan dari FeatureAccess::FEATURES_MAP
 * agar data tetap konsisten dan tidak perlu dikelola di dua tempat.
 *
 * Jalankan: php artisan db:seed --class=UmkmLevelSeeder
 */
class UmkmLevelSeeder extends Seeder
{
    public function run(): void
    {
        // v1.1: tidak lagi truncate. updateOrCreate per kode agar id level dan
        // level_id UMKM lama tidak berubah (BR-14).

        // ================================================================
        // LEVEL 1 — Dasar / Starter
        // ================================================================
        UmkmLevel::updateOrCreate(['kode' => 'L1'], [
            'nama_level'    => 'Level 1 - Dasar',
            'urutan'        => 1,
            'skor_min'      => 4,
            'skor_max'      => 6,
            'deskripsi'     => 'Fitur dasar: transaksi harian, bahan baku, produk, kasir, iuran, '
                             . 'jurnal umum, laporan penjualan & pembelian.',
            'fitur'         => FeatureAccess::forLevel(1),
            'iuran_bulanan' => 0,
        ]);

        // ================================================================
        // LEVEL 2 — Menengah / Pro
        // ================================================================
        UmkmLevel::updateOrCreate(['kode' => 'L2'], [
            'nama_level'    => 'Level 2 - Berkembang',
            'urutan'        => 2,
            'skor_min'      => 7,
            'skor_max'      => 9,
            'deskripsi'     => 'Semua fitur Level 1, ditambah: kartu stok, buku besar, '
                             . 'laba rugi, dan manajemen piutang pelanggan.',
            'fitur'         => FeatureAccess::forLevel(2),
            'iuran_bulanan' => 20000,
        ]);

        // ================================================================
        // LEVEL 3 — Lanjut / Expert
        // ================================================================
        UmkmLevel::updateOrCreate(['kode' => 'L3'], [
            'nama_level'    => 'Level 3 - Lanjut',
            'urutan'        => 3,
            'skor_min'      => 10,
            'skor_max'      => 12,
            'deskripsi'     => 'Akses penuh semua fitur termasuk COA, produksi, '
                             . 'perubahan modal, dan arus kas.',
            'fitur'         => FeatureAccess::forLevel(3),
            'iuran_bulanan' => 30000,
        ]);
    }
}
