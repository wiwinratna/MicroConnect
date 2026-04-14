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
        Schema::disableForeignKeyConstraints();
        UmkmLevel::truncate();
        Schema::enableForeignKeyConstraints();

        // ================================================================
        // LEVEL 1 — Dasar / Starter
        // ================================================================
        UmkmLevel::create([
            'kode'          => 'L1',
            'nama_level'    => 'Level 1 - Dasar',
            'deskripsi'     => 'Fitur dasar: transaksi harian, bahan baku, produk, kasir, iuran, '
                             . 'jurnal umum, laporan penjualan & pembelian.',
            'fitur'         => FeatureAccess::forLevel(1),
            'iuran_bulanan' => 0,
        ]);

        // ================================================================
        // LEVEL 2 — Menengah / Pro
        // ================================================================
        UmkmLevel::create([
            'kode'          => 'L2',
            'nama_level'    => 'Level 2 - Menengah',
            'deskripsi'     => 'Semua fitur Level 1, ditambah: kartu stok, buku besar, '
                             . 'laba rugi, dan manajemen piutang pelanggan.',
            'fitur'         => FeatureAccess::forLevel(2),
            'iuran_bulanan' => 20000,
        ]);

        // ================================================================
        // LEVEL 3 — Lanjut / Expert
        // ================================================================
        UmkmLevel::create([
            'kode'          => 'L3',
            'nama_level'    => 'Level 3 - Lanjut',
            'deskripsi'     => 'Akses penuh semua fitur termasuk COA, produksi, '
                             . 'perubahan modal, dan arus kas.',
            'fitur'         => FeatureAccess::forLevel(3),
            'iuran_bulanan' => 30000,
        ]);
    }
}
