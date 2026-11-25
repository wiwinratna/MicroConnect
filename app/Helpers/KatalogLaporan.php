<?php

namespace App\Helpers;

/**
 * Katalog laporan yang dapat diatur Admin per level (FR-18, Tabel 3.9).
 * Tiap laporan memuat kunci fitur halaman dan ekspornya di FeatureAccess.
 * Fitur lain (iuran, tiket, dan sebagainya) tidak diatur lewat katalog ini.
 */
class KatalogLaporan
{
    /** kode => [nama, [kunci fitur]] */
    public const DAFTAR = [
        'jurnal_umum'       => ['Jurnal Umum', ['jurnal_umum', 'export_jurnal_umum']],
        'laporan_pembelian' => ['Laporan Pembelian', ['laporan_pembelian', 'export_laporan_pembelian']],
        'laporan_penjualan' => ['Laporan Penjualan', ['laporan_penjualan', 'export_laporan_penjualan']],
        'buku_besar'        => ['Buku Besar', ['buku_besar', 'export_buku_besar']],
        'laba_rugi'         => ['Laba Rugi', ['laba_rugi', 'export_laba_rugi']],
        'kartu_stok'        => ['Kartu Stok dan Rekap Stok', ['kartu_stok', 'export_kartu_stok', 'export_rekap_stok']],
        'piutang'           => ['Piutang dan Laporan Piutang', ['piutang', 'export_laporan_piutang']],
        'perubahan_modal'   => ['Perubahan Modal', ['perubahan_modal', 'export_perubahan_modal']],
        'arus_kas'          => ['Arus Kas', ['arus_kas', 'export_arus_kas']],
    ];

    /** Semua kunci fitur yang dikelola katalog. */
    public static function semuaKunci(): array
    {
        return array_values(array_unique(array_merge(...array_column(self::DAFTAR, 1))));
    }

    /** Apakah laporan aktif pada daftar fitur (berdasarkan kunci halaman, elemen pertama). */
    public static function aktifDi(string $kode, array $fitur): bool
    {
        return in_array(self::DAFTAR[$kode][1][0], $fitur, true);
    }

    /** Terapkan pilihan laporan ke daftar fitur, tanpa menyentuh fitur di luar katalog. */
    public static function terapkan(array $fitur, array $kodeAktif): array
    {
        $fitur = array_values(array_diff($fitur, self::semuaKunci()));

        foreach ($kodeAktif as $kode) {
            if (isset(self::DAFTAR[$kode])) {
                $fitur = array_merge($fitur, self::DAFTAR[$kode][1]);
            }
        }

        return array_values(array_unique($fitur));
    }
}
