<?php

if (!function_exists('rupiah')) {
    /**
     * Format angka ke format rupiah: Rp 1.500.000
     *
     * @param float|int|null $amount
     * @param int $decimals  Jumlah desimal (default 0)
     * @return string
     */
    function rupiah($amount, int $decimals = 0): string
    {
        if ($amount === null || $amount === '') {
            return 'Rp 0';
        }
        return 'Rp ' . number_format((float) $amount, $decimals, ',', '.');
    }
}

if (!function_exists('rupiah_raw')) {
    /**
     * Konversi string rupiah terformat kembali ke angka mentah.
     * Contoh: "Rp 1.500.000" → 1500000
     *
     * @param string $formatted
     * @return float
     */
    function rupiah_raw(string $formatted): float
    {
        // Hapus "Rp", spasi, titik (pemisah ribuan)
        $clean = preg_replace('/[Rp\s\.]/', '', $formatted);
        // Ganti koma desimal ke titik
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }
}

if (!function_exists('format_angka')) {
    /**
     * Format angka umum (bukan uang).
     * Jika bulat, tampil tanpa desimal.
     * Jika pecahan, tampilkan desimal (maks 2 atau 3) tanpa trailing zero.
     *
     * @param float|int|string|null $amount
     * @return string
     */
    function format_angka($amount): string
    {
        if ($amount === null || $amount === '') {
            return '0';
        }
        $amount = (float) $amount;
        
        // Jika angka bulat sempurna
        if (floor($amount) == $amount) {
            return number_format($amount, 0, ',', '.');
        }

        // Jika pecahan, default 2 desimal maksimum
        $formatted = number_format($amount, 2, ',', '.');
        
        // Hapus nol berlebih di belakang koma (contoh: ,50 -> ,5)
        $formatted = rtrim($formatted, '0');
        // Hapus koma jika ternyata hanya koma yang tersisa (misal ,00 menjadi kosong)
        $formatted = rtrim($formatted, ',');
        
        return $formatted;
    }
}

if (!function_exists('feature_can')) {
    /**
     * Cek apakah user UMKM yang login boleh mengakses fitur tertentu.
     *
     * Shorthand untuk \App\Helpers\FeatureAccess::can($feature)
     *
     * @param string $feature  Kunci fitur (misal: 'buku_besar', 'kartu_stok')
     * @return bool
     *
     * Penggunaan di Blade:
     *   @if(feature_can('buku_besar')) ... @endif
     *
     * Penggunaan di PHP:
     *   if (!feature_can('buku_besar')) abort(403);
     */
    function feature_can(string $feature): bool
    {
        return \App\Helpers\FeatureAccess::can($feature);
    }
}

if (!function_exists('feature_level')) {
    /**
     * Kembalikan level UMKM user yang sedang login (integer 1–3).
     */
    function feature_level(): int
    {
        return \App\Helpers\FeatureAccess::getCurrentLevel();
    }
}
