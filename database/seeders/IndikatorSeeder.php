<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Instrumen kuesioner — Tabel 3.6 (teks persis seperti master prompt Bagian 4).
 * Skor ada di data (BR-09, BR-12). Aman dijalankan ulang:
 * indikator dikenali dari urutan, pilihan dari (indikator, urutan).
 *
 * Jalankan: php artisan db:seed --class=IndikatorSeeder
 */
class IndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // [nama, pertanyaan, [ [teks, skor, di_luar_batas_mikro, nilai_min, nilai_max, contoh], ... ]]
        $data = [
            1 => ['Omzet tahunan', 'Berapa hasil penjualan (omzet) usaha Anda dalam satu tahun terakhir?', [
                ['Sampai dengan Rp500 juta', 1, false, null, 500000000, null],
                ['Lebih dari Rp500 juta sampai dengan Rp1,2 miliar', 2, false, 500000000, 1200000000, null],
                ['Lebih dari Rp1,2 miliar sampai dengan Rp2 miliar', 3, false, 1200000000, 2000000000, null],
                ['Lebih dari Rp2 miliar', 3, true, 2000000000, null, null],
            ]],
            2 => ['Modal usaha', 'Berapa modal usaha Anda, tidak termasuk tanah dan bangunan tempat usaha?', [
                ['Sampai dengan Rp100 juta', 1, false, null, 100000000, null],
                ['Lebih dari Rp100 juta sampai dengan Rp500 juta', 2, false, 100000000, 500000000, null],
                ['Lebih dari Rp500 juta sampai dengan Rp1 miliar', 3, false, 500000000, 1000000000, null],
                ['Lebih dari Rp1 miliar', 3, true, 1000000000, null, null],
            ]],
            3 => ['Jumlah tenaga kerja', 'Berapa orang yang menjalankan kegiatan usaha sehari-hari (termasuk pemilik)?', [
                ['1 orang (dijalankan pemilik sendiri)', 1, false, 1, 1, null],
                ['2–3 orang (termasuk pemilik)', 2, false, 2, 3, null],
                ['4 orang (termasuk pemilik)', 3, false, 4, 4, null],
                ['Lebih dari 4 orang', 3, true, 5, null, null],
            ]],
            4 => ['Kebiasaan pencatatan', 'Bagaimana kebiasaan pencatatan keuangan usaha Anda saat ini?', [
                ['Tidak mencatat sama sekali atau hanya melakukan pencatatan setiap minggu/bulan.', 1, false, null, null,
                    'Contoh: menghitung keuntungan dari sisa uang atau kumpulan nota pada akhir minggu/bulan.'],
                ['Melakukan pencatatan setiap hari.', 2, false, null, null,
                    'Contoh: pada akhir hari merekap uang masuk dan keluar di buku tulis sederhana atau dari kumpulan nota.'],
                ['Melakukan pencatatan setiap terjadi transaksi.', 3, false, null, null,
                    'Contoh: setiap penjualan, pembelian, dan pembayaran langsung dicatat di buku kas atau aplikasi, lalu dibukukan per akun seperti buku besar.'],
            ]],
        ];

        foreach ($data as $urutan => [$nama, $pertanyaan, $pilihan]) {
            DB::table('indikator')->updateOrInsert(
                ['urutan' => $urutan],
                [
                    'nama' => $nama, 'pertanyaan' => $pertanyaan,
                    'sumber_data' => 'pilihan_jawaban', 'aktif' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]
            );
            $indikatorId = DB::table('indikator')->where('urutan', $urutan)->value('id');

            foreach ($pilihan as $i => [$teks, $skor, $luar, $min, $max, $contoh]) {
                DB::table('pilihan_jawaban')->updateOrInsert(
                    ['indikator_id' => $indikatorId, 'urutan' => $i + 1],
                    [
                        'teks' => $teks, 'contoh' => $contoh,
                        'nilai_min' => $min, 'nilai_max' => $max,
                        'skor' => $skor, 'di_luar_batas_mikro' => $luar,
                        'created_at' => $now, 'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
