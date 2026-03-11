<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Aset', '111', 'Kas', 'Debit'],
            ['Aset', '112', 'Persediaan Bahan Baku', 'Debit'],
            ['Aset', '113', 'Persediaan Produk Jadi', 'Debit'],

            ['Modal', '311', 'Modal', 'Kredit'],

            ['Pendapatan', '400', 'Pendapatan Penjualan', 'Kredit'],

            ['Beban', '500', 'Beban Bahan Baku', 'Debit'],
            ['Beban', '510', 'Beban Overhead', 'Debit'],
            ['Beban', '520', 'Harga Pokok Penjualan', 'Debit'],
        ];

        foreach ($data as $d) {
            Coa::create([
                'header_akun' => $d[0],
                'kode_akun'   => $d[1],
                'nama_akun'   => $d[2],
                'posisi_dr_cr'=> $d[3],
                'umkm_id'     => 1, // ganti sesuai umkm kamu
            ]);
        }
    }
}
