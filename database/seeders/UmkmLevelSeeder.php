<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UmkmLevel;
use Illuminate\Support\Facades\Schema;

class UmkmLevelSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        UmkmLevel::truncate(); // biar bersih kalau di-run ulang
        Schema::enableForeignKeyConstraints();

        UmkmLevel::create([
            'kode'         => 'L1',
            'nama_level'   => 'Level 1 - Basic',
            'deskripsi'    => 'Cocok untuk UMKM yang baru mulai mencatat keuangan sederhana.',
            'fitur'        => [
                'pemasukan',
                'pengeluaran',
                'cashflow_sederhana',
            ],
            'iuran_bulanan' => 0, // misal gratis dulu
        ]);

        UmkmLevel::create([
            'kode'         => 'L2',
            'nama_level'   => 'Level 2 - Menengah',
            'deskripsi'    => 'Sudah mendukung produk, stok, dan penjualan.',
            'fitur'        => [
                'pemasukan',
                'pengeluaran',
                'master_produk',
                'bahan_baku',
                'stok',
                'pembelian',
                'penjualan',
                'laporan_penjualan',
                'laporan_stok',
            ],
            'iuran_bulanan' => 20000,
        ]);

        UmkmLevel::create([
            'kode'         => 'L3',
            'nama_level'   => 'Level 3 - Lengkap',
            'deskripsi'    => 'Fitur lengkap sampai laporan keuangan (laba rugi, modal, neraca).',
            'fitur'        => [
                'pemasukan',
                'pengeluaran',
                'master_produk',
                'bahan_baku',
                'stok',
                'pembelian',
                'penjualan',
                'piutang',
                'jurnal',
                'buku_besar',
                'laba_rugi',
                'perubahan_modal',
                'neraca',
            ],
            'iuran_bulanan' => 30000,
        ]);
    }
}
