<?php

namespace Database\Seeders\AkunUji;

use Database\Seeders\Demo\DataDemo;

/**
 * Data akun uji (fiktif). Transaksinya meminjam pola data demo; yang berbeda hanya
 * identitas akun, jenis usaha, dan level.
 */
final class DataAkunUji
{
    public static function umkm(): array
    {
        [$produksi, $jasaStok, $jasa] = DataDemo::umkmAktif();

        // Penjualan kredit (piutang) baru tersedia mulai level 2.
        $produksiTanpaKredit = array_diff_key($produksi, ['kredit' => null]);

        // Usaha dagang: tidak mengolah bahan, jadi tanpa bahan baku dan komposisi.
        $dagang = [
            'modal'  => 10000000,
            'bahan'  => [],
            'produk' => [
                'Beras 5 kg'      => [75000, [], 'karung'],
                'Gula Pasir 1 kg' => [18000, [], 'bungkus'],
            ],
            'jual'   => [[10, 'Beras 5 kg', 20], [20, 'Beras 5 kg', 15], [20, 'Gula Pasir 1 kg', 30]],
            'beban'  => [['612', 'Sewa tempat', 700000], ['610', 'Listrik', 150000]],
            'kredit' => ['pelanggan' => 'Warung Uji Berkah', 'produk' => 'Gula Pasir 1 kg', 'jumlah' => 12],
        ];

        return [
            self::akun($produksiTanpaKredit, 1, 'barang1', 'Uji Barang Level 1', 'barang', 'L1', ['mengolah_bahan' => '1']),
            self::akun($produksi, 2, 'barang2', 'Uji Barang Level 2', 'barang', 'L2', ['mengolah_bahan' => '1']),
            self::akun($produksi, 3, 'barang3', 'Uji Barang Level 3', 'barang', 'L3', ['mengolah_bahan' => '1']),
            self::akun($dagang, 4, 'dagang', 'Uji Dagang Level 3', 'barang', 'L3', ['mengolah_bahan' => '0']),
            self::akun($jasaStok, 5, 'jasa', 'Uji Jasa Level 3', 'jasa', 'L3', ['punya_persediaan' => '1']),
            self::akun($jasa, 6, 'jasanonstok', 'Uji Jasa Tanpa Stok', 'jasa', 'L3', ['punya_persediaan' => '0']),
        ];
    }

    private static function akun(
        array $pola, int $nomor, string $username, string $usaha,
        string $jenis, string $level, array $lanjutan
    ): array {
        $pola['level'] = $level;
        $pola['daftar'] = [
            'form' => [
                'nama_pemilik' => 'Pemilik ' . $usaha,
                'email'        => $username . '@contoh.test',
                'no_wa'        => '08139900000' . $nomor,
                'nama_usaha'   => $usaha,
                'alamat'       => "Jl. Uji Coba No. {$nomor}, Bengkalis",
                'rumpun'       => $jenis,
                'persetujuan_data_pribadi' => '1',
            ] + $lanjutan,
            'kategori' => $jenis === 'jasa' ? 'Jasa perorangan' : 'Jasa boga',
            'jawaban'  => 'aaaa',
        ];

        return $pola;
    }
}
