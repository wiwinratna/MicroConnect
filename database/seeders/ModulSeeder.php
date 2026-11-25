<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Modul (kode fitur v1.0) dan aturan tampilan per rumpun — Tabel 3.8.
 * Keputusan E: bahan_baku rumpun barang = jika_melakukan_produksi.
 * Aman dijalankan ulang.
 *
 * Jalankan: php artisan db:seed --class=ModulSeeder
 */
class ModulSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $modul = [
            'bahan_baku'        => 'Bahan Baku',
            'kartu_stok'        => 'Kartu Stok',
            'metode_persediaan' => 'Metode Penilaian Persediaan',
            'produksi'          => 'Produksi',
            'produk'            => 'Produk',
            'pembelian'         => 'Pembelian',
            'penjualan'         => 'Penjualan',
            'beban'             => 'Beban',
            'laporan'           => 'Laporan',
        ];

        $ids = [];
        foreach ($modul as $kode => $nama) {
            DB::table('modul')->updateOrInsert(
                ['kode' => $kode],
                ['nama' => $nama, 'aktif' => true, 'created_at' => $now, 'updated_at' => $now]
            );
            $ids[$kode] = DB::table('modul')->where('kode', $kode)->value('id');
        }

        // [kode modul, rumpun, kondisi, label_tampil]
        $aturan = [
            ['bahan_baku',        'barang', 'jika_melakukan_produksi',  null],
            ['bahan_baku',        'jasa',   'jika_memiliki_persediaan', null],
            ['kartu_stok',        'barang', 'selalu',                   null],
            ['kartu_stok',        'jasa',   'jika_memiliki_persediaan', null],
            ['metode_persediaan', 'barang', 'selalu',                   null],
            ['metode_persediaan', 'jasa',   'jika_memiliki_persediaan', null],
            ['produksi',          'barang', 'jika_melakukan_produksi',  null],
            ['produksi',          'jasa',   'tidak_ditampilkan',        null],
            ['produk',            'barang', 'selalu',                   'Produk'],
            ['produk',            'jasa',   'selalu',                   'Daftar Layanan Jasa'],
            ['pembelian',         'barang', 'selalu',                   null],
            ['pembelian',         'jasa',   'selalu',                   null],
            ['penjualan',         'barang', 'sesuai_level',             null],
            ['penjualan',         'jasa',   'sesuai_level',             null],
            ['beban',             'barang', 'sesuai_level',             null],
            ['beban',             'jasa',   'sesuai_level',             null],
            ['laporan',           'barang', 'sesuai_level',             null],
            ['laporan',           'jasa',   'sesuai_level',             null],
        ];

        foreach ($aturan as [$kode, $rumpun, $kondisi, $label]) {
            DB::table('aturan_modul')->updateOrInsert(
                ['modul_id' => $ids[$kode], 'rumpun' => $rumpun],
                ['kondisi' => $kondisi, 'label_tampil' => $label, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
