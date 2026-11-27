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
            'deskripsi'     => 'Fitur dasar: transaksi harian, bahan baku, produk, kasir, iuran, '
                             . 'jurnal umum, laporan penjualan & pembelian.',
            'iuran_bulanan' => 0,
        ]);

        // ================================================================
        // LEVEL 2 — Menengah / Pro
        // ================================================================
        UmkmLevel::updateOrCreate(['kode' => 'L2'], [
            'nama_level'    => 'Level 2 - Berkembang',
            'urutan'        => 2,
            'deskripsi'     => 'Semua fitur Level 1, ditambah: kartu stok, buku besar, '
                             . 'laba rugi, dan manajemen piutang pelanggan.',
            'iuran_bulanan' => 20000,
        ]);

        // ================================================================
        // LEVEL 3 — Lanjut / Expert
        // ================================================================
        UmkmLevel::updateOrCreate(['kode' => 'L3'], [
            'nama_level'    => 'Level 3 - Lanjut',
            'urutan'        => 3,
            'deskripsi'     => 'Akses penuh semua fitur termasuk COA, produksi, '
                             . 'perubahan modal, dan arus kas.',
            'iuran_bulanan' => 30000,
        ]);

        // Batas skor hanya diisi bila masih kosong; Admin dapat mengubahnya (BR-09).
        foreach (['L1' => [4, 6], 'L2' => [7, 9], 'L3' => [10, 12]] as $kode => [$min, $max]) {
            UmkmLevel::where('kode', $kode)->whereNull('skor_min')->update(['skor_min' => $min]);
            UmkmLevel::where('kode', $kode)->whereNull('skor_max')->update(['skor_max' => $max]);
        }

        // v1.1 (FR-18): `fitur` diisi dari FEATURES_MAP hanya jika masih kosong, supaya
        // pengaturan laporan per level oleh Admin tidak tertimpa saat seeder dijalankan ulang.
        foreach ([1, 2, 3] as $nomor) {
            $level = UmkmLevel::where('kode', "L{$nomor}")->first();

            if ($level && empty($level->fitur)) {
                $level->update(['fitur' => FeatureAccess::forLevel($nomor)]);
            }
        }
    }
}
