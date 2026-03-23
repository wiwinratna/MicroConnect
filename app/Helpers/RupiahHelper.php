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
