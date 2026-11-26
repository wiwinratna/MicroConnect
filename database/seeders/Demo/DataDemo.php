<?php

namespace Database\Seeders\Demo;

/**
 * Data demo K5: seluruhnya fiktif. Huruf jawaban: a = pilihan pertama, dst.
 * `bahan`: nama => [satuan, stok awal, harga, jumlah beli per bulan].
 * `produk`: nama => [harga jual, komposisi per unit (nama bahan => jumlah)].
 * `jual`: daftar [tanggal dalam bulan, nama produk, jumlah], diulang tiap bulan.
 */
final class DataDemo
{
    public static function umkmAktif(): array
    {
        return [
            [
                'daftar' => self::daftar(1, 'Rina Contoh', 'demo_keripik', 'Keripik Contoh Melati',
                    'barang', 'Jasa boga', 'ccbb', ['mengolah_bahan' => '1']),
                'modal' => 15000000,
                'bahan' => [
                    'Singkong'       => ['kg', 10, 6000, 60],
                    'Minyak goreng'  => ['liter', 5, 18000, 20],
                    'Kemasan 250 g'  => ['pcs', 50, 700, 150],
                ],
                'produk' => [
                    'Keripik Original 250 g' => [18000, ['Singkong' => 0.5, 'Minyak goreng' => 0.15, 'Kemasan 250 g' => 1]],
                    'Keripik Balado 250 g'   => [20000, ['Singkong' => 0.5, 'Minyak goreng' => 0.15, 'Kemasan 250 g' => 1]],
                ],
                'jual'  => [[10, 'Keripik Original 250 g', 50], [20, 'Keripik Original 250 g', 40], [20, 'Keripik Balado 250 g', 20]],
                'beban' => [['610', 'Listrik produksi', 250000], ['613', 'Upah pekerja', 1500000]],
                // Satu penjualan kredit pada bulan terakhir.
                'kredit' => ['pelanggan' => 'Toko Contoh Sejahtera', 'produk' => 'Keripik Balado 250 g', 'jumlah' => 10],
            ],
            [
                'daftar' => self::daftar(2, 'Doni Contoh', 'demo_bengkel', 'Bengkel Contoh Jaya',
                    'jasa', 'Jasa perorangan', 'aabc', ['punya_persediaan' => '1']),
                'modal' => 8000000,
                'bahan' => [
                    'Oli mesin'  => ['botol', 5, 45000, 20],
                    'Kampas rem' => ['pcs', 4, 40000, 12],
                ],
                'produk' => [
                    'Jasa Ganti Oli'        => [75000, ['Oli mesin' => 1]],
                    'Jasa Ganti Kampas Rem' => [90000, ['Kampas rem' => 1]],
                ],
                'jual'  => [[10, 'Jasa Ganti Oli', 9], [20, 'Jasa Ganti Oli', 8], [20, 'Jasa Ganti Kampas Rem', 10]],
                'beban' => [['612', 'Sewa tempat', 600000], ['610', 'Listrik', 150000]],
            ],
            [
                'daftar' => self::daftar(3, 'Sari Contoh', 'demo_pangkas', 'Pangkas Contoh Rapi',
                    'jasa', 'Jasa perorangan', 'aaaa', ['punya_persediaan' => '0']),
                'modal' => 3000000,
                'bahan'  => [],
                'produk' => [
                    'Pangkas Rambut Dewasa' => [20000, []],
                    'Pangkas Rambut Anak'   => [15000, []],
                ],
                'jual'  => [[10, 'Pangkas Rambut Dewasa', 45], [20, 'Pangkas Rambut Dewasa', 40], [20, 'Pangkas Rambut Anak', 25]],
                'beban' => [['612', 'Sewa tempat', 500000], ['610', 'Listrik', 100000]],
            ],
        ];
    }

    /** Pengajuan yang dibiarkan `menunggu` untuk demo verifikasi. */
    public static function pengajuanMenunggu(): array
    {
        return self::daftar(4, 'Wati Contoh', 'demo_tenun', 'Tenun Contoh Lestari',
            'barang', 'Usaha kreatif', 'aabc', ['mengolah_bahan' => '1']);
    }

    private static function daftar(
        int $nomor, string $pemilik, string $username, string $usaha,
        string $jenis, string $kategori, string $jawaban, array $lanjutan
    ): array {
        return [
            'form' => [
                'nama_pemilik' => $pemilik,
                'email'        => str_replace('demo_', '', $username) . '@contoh.test',
                'username'     => $username,
                'no_wa'        => '08130000000' . $nomor,
                'nama_usaha'   => $usaha,
                'alamat'       => "Jl. Contoh No. {$nomor}, Bengkalis",
                'rumpun'       => $jenis,
                'persetujuan_data_pribadi' => '1',
            ] + $lanjutan,
            'kategori' => $kategori,
            'jawaban'  => $jawaban,
        ];
    }
}
