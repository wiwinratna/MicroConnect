<?php

namespace App\Helpers;

/**
 * FeatureAccess — Pusat kendali akses fitur UMKM berdasarkan level.
 *
 * Cara pakai:
 *   FeatureAccess::can('buku_besar')         → bool (dari session user)
 *   FeatureAccess::canLevel(2, 'buku_besar') → bool (eksplisit dengan level)
 *   FeatureAccess::forLevel(2)               → array fitur yang bisa diakses
 *
 * Untuk menambah fitur baru:
 *   1. Tambahkan 'kunci_fitur' ke array FEATURES_MAP di level yang sesuai.
 *   2. Panggil FeatureAccess::can('kunci_fitur') di controller / view.
 *   3. Daftarkan route ke middleware 'feature:kunci_fitur' di web.php.
 */
class FeatureAccess
{
    /**
     * Mapping fitur per level.
     * Level 3 otomatis mendapat semua fitur (lihat forLevel()).
     *
     * Format: ['level' => ['kunci_fitur', ...]]
     *
     * Konvensi penamaan kunci:
     *   - modul tunggal        : 'bahan_baku', 'produk', 'pembelian'
     *   - sub-fitur laporan    : 'laporan_pembelian', 'laporan_penjualan'
     *   - laporan keuangan     : 'jurnal_umum', 'buku_besar', 'laba_rugi'
     *   - export turunan       : 'export_jurnal_umum', 'export_buku_besar', dsb.
     */
    public const FEATURES_MAP = [

        // =================================================================
        // LEVEL 1 — Dasar / Starter
        // =================================================================
        1 => [
            'dashboard',
            'profile',
            'bahan_baku',
            'produk',
            'pembelian',
            'penjualan',
            'kasir',
            'beban',
            'iuran',
            'tickets',

            // Laporan keuangan — hanya Jurnal Umum
            'jurnal_umum',
            'laporan_pembelian',
            'laporan_penjualan',

            // Export turunan
            'export_jurnal_umum',
            'export_laporan_pembelian',
            'export_laporan_penjualan',
        ],

        // =================================================================
        // LEVEL 2 — Menengah / Pro
        //   = Level 1 + akses laporan keuangan lebih lengkap
        // =================================================================
        2 => [
            // Semua Level 1 (+)
            'kartu_stok',
            'buku_besar',
            'laba_rugi',
            'piutang',

            // Export tambahan
            'export_buku_besar',
            'export_laba_rugi',
            'export_kartu_stok',
            'export_rekap_stok',
            'export_laporan_piutang',
        ],

        // =================================================================
        // LEVEL 3 — Lanjut / Expert
        //   = Semua fitur, tidak perlu didaftarkan satu-satu
        //     (lihat forLevel() & can() yang otomatis return true)
        // =================================================================
        3 => [
            'coa',
            'produksi',
            'perubahan_modal',
            'arus_kas',
            'export_perubahan_modal',
            'export_arus_kas',
        ],
    ];

    // ------------------------------------------------------------------
    // PUBLIC API
    // ------------------------------------------------------------------

    /**
     * Cek apakah user yang sedang login boleh mengakses fitur tertentu.
     */
    public static function can(string $feature): bool
    {
        $level = static::getCurrentLevel();
        return static::canLevel($level, $feature);
    }

    /**
     * Cek atas nama level tertentu (tanpa AUTH dependency).
     */
    public static function canLevel(int $level, string $feature): bool
    {
        // Level 3 mendapat semua akses
        if ($level >= 3) {
            return true;
        }

        // Kumpulkan semua fitur yang diizinkan sampai level ini (kumulatif)
        $allowed = static::forLevel($level);

        return in_array($feature, $allowed, true);
    }

    /**
     * Kembalikan semua fitur yang boleh diakses untuk level tertentu.
     * Level bersifat kumulatif (level 2 = level 1 + level 2).
     */
    public static function forLevel(int $level): array
    {
        if ($level >= 3) {
            // Level 3 = semua fitur dari semua level
            return array_unique(array_merge(
                static::FEATURES_MAP[1] ?? [],
                static::FEATURES_MAP[2] ?? [],
                static::FEATURES_MAP[3] ?? [],
            ));
        }

        $features = [];
        for ($i = 1; $i <= $level; $i++) {
            $features = array_merge($features, static::FEATURES_MAP[$i] ?? []);
        }

        return array_unique($features);
    }

    /**
     * Ambil level UMKM user yang sedang login.
     * Default ke 1 jika tidak ada level.
     */
    public static function getCurrentLevel(): int
    {
        $user = auth()->user();

        if (!$user || !$user->umkm || !$user->umkm->level) {
            return 1;
        }

        // Ekstrak angka dari kode (misal: 'L1' → 1, 'L2' → 2)
        $kode  = $user->umkm->level->kode ?? '';
        $angka = (int) preg_replace('/[^0-9]/', '', $kode);

        // Fallback ke level_id jika kode tidak mengandung angka
        if ($angka < 1) {
            $angka = (int) $user->umkm->level_id;
        }

        return max(1, min(3, $angka)); // Clamp 1–3
    }

    /**
     * Alias pendek untuk Blade: @if(feature_can('buku_besar'))
     * (didaftarkan via helper global di RupiahHelper.php atau file helper baru)
     */
    public static function gate(string ...$features): bool
    {
        foreach ($features as $f) {
            if (!static::can($f)) {
                return false;
            }
        }
        return true;
    }
}
