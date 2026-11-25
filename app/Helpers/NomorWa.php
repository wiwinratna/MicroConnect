<?php

namespace App\Helpers;

/**
 * Normalisasi nomor WhatsApp ke format 62xxxxxxxxxx.
 * Contoh: "0812-3456-7890" menjadi "6281234567890"; "+62 812 3456 7890" juga menjadi "6281234567890".
 */
class NomorWa
{
    public static function normalisasi(?string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor);

        if ($angka === '') {
            return '';
        }

        if (str_starts_with($angka, '0')) {
            return '62' . substr($angka, 1);
        }

        if (str_starts_with($angka, '8')) {
            return '62' . $angka;
        }

        return $angka;
    }
}
