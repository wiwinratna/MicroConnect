<?php

namespace App\Helpers;

class UnitConverter
{
    /**
     * Daftar satuan dengan faktor konversi ke satuan DASAR masing-masing dimensi.
     *
     * Dimensi & Satuan Dasar:
     *   - weight  → gram (g)
     *   - volume  → ml
     *   - count   → pcs
     *   - portion → porsi
     *
     * Catatan: pack ↔ pcs TIDAK dikonversi global karena 1 pack bisa
     * berbeda per bahan (misal: 1 pack telur = 10 pcs, 1 pack keju = 1 pcs).
     * Gunakan satuan dasar bahan yang sesuai saat input pembelian/resep.
     */
    protected static array $units = [
        // ============ BERAT (base: gram) ============
        'gram'     => ['dim' => 'weight', 'factor' => 1,      'label' => 'Gram (g / gr)'],
        'g'        => ['dim' => 'weight', 'factor' => 1,      'label' => 'Gram (g)'],
        'gr'       => ['dim' => 'weight', 'factor' => 1,      'label' => 'Gram (gr)'],
        'kg'       => ['dim' => 'weight', 'factor' => 1000,   'label' => 'Kilogram (kg)'],
        'kilogram' => ['dim' => 'weight', 'factor' => 1000,   'label' => 'Kilogram'],
        'mg'       => ['dim' => 'weight', 'factor' => 0.001,  'label' => 'Miligram (mg)'],

        // ============ VOLUME (base: ml) ============
        'ml'            => ['dim' => 'volume', 'factor' => 1,      'label' => 'Mililiter (ml)'],
        'milliliter'    => ['dim' => 'volume', 'factor' => 1,      'label' => 'Mililiter'],
        'l'             => ['dim' => 'volume', 'factor' => 1000,   'label' => 'Liter (l)'],
        'liter'         => ['dim' => 'volume', 'factor' => 1000,   'label' => 'Liter'],
        'cc'            => ['dim' => 'volume', 'factor' => 1,      'label' => 'cc (cm³)'],

        // ============ JUMLAH / SATUAN HITUNG (base: pcs) ============
        'pcs'   => ['dim' => 'count', 'factor' => 1,  'label' => 'Pcs'],
        'buah'  => ['dim' => 'count', 'factor' => 1,  'label' => 'Buah'],
        'butir' => ['dim' => 'count', 'factor' => 1,  'label' => 'Butir'],
        'lembar'=> ['dim' => 'count', 'factor' => 1,  'label' => 'Lembar'],
        'biji'  => ['dim' => 'count', 'factor' => 1,  'label' => 'Biji'],
        'lusin' => ['dim' => 'count', 'factor' => 12, 'label' => 'Lusin (12 pcs)'],

        // ============ PORSI (base: porsi) ============
        'porsi'   => ['dim' => 'portion', 'factor' => 1, 'label' => 'Porsi'],
        'serving' => ['dim' => 'portion', 'factor' => 1, 'label' => 'Serving'],

        // ============ PACK — TIDAK DIKONVERSI GLOBAL ============
        // pack dibiarkan sebagai unit tersendiri (tidak punya faktor ke pcs)
        // karena 1 pack bisa = 1, 10, 12 pcs tergantung bahan.
        'pack'  => ['dim' => 'pack', 'factor' => 1, 'label' => 'Pack'],
        'bungkus' => ['dim' => 'pack', 'factor' => 1, 'label' => 'Bungkus'],
        'sachet'  => ['dim' => 'pack', 'factor' => 1, 'label' => 'Sachet'],
        'kaleng'  => ['dim' => 'pack', 'factor' => 1, 'label' => 'Kaleng'],
        'botol'   => ['dim' => 'pack', 'factor' => 1, 'label' => 'Botol'],
        'dus'     => ['dim' => 'pack', 'factor' => 1, 'label' => 'Dus'],
    ];

    /**
     * Kembalikan daftar satuan untuk UI dropdown.
     * Format: ['value' => 'gram', 'label' => 'Gram (g / gr)']
     */
    public static function getUiOptions(): array
    {
        $seen = [];
        $options = [];

        foreach (self::$units as $key => $meta) {
            // Gunakan label unik untuk display, hindupkan duplikat label
            $label = $meta['label'];
            if (!in_array($label, $seen)) {
                $seen[] = $label;
                $options[] = ['value' => $key, 'label' => $label];
            }
        }

        return $options;
    }

    /**
     * Normalisasi input satuan dari user (lowercase, trim, alias).
     */
    protected static function normalise(string $unit): string
    {
        $u = strtolower(trim($unit));

        // Alias umum
        $aliases = [
            'ltr'  => 'liter',
            'kilo' => 'kg',
            'gr'   => 'gram',
            'g'    => 'gram',
            'cc'   => 'ml',
        ];

        return $aliases[$u] ?? $u;
    }

    /**
     * Kembalikan info dimensi & faktor satuan, atau null kalau tidak dikenal.
     */
    public static function info(string $unit): ?array
    {
        $u = self::normalise($unit);
        return isset(self::$units[$u]) ? self::$units[$u] : null;
    }

    /**
     * Konversi qty dari satuan tertentu ke satuan DASAR dimensinya.
     * Return: [qty_dasar, unit_dasar]
     * Contoh: toBase(1, 'kg') → [1000, 'gram']
     */
    public static function toBase(float $qty, string $unit): array
    {
        $info = self::info($unit);

        if (!$info) {
            return [$qty, $unit]; // tidak dikenal, kembalikan apa adanya
        }

        $dim    = $info['dim'];
        $factor = $info['factor'];

        // Cari unit dasar (factor = 1) untuk dimensi ini
        $baseUnit = null;
        foreach (self::$units as $u => $meta) {
            if ($meta['dim'] === $dim && $meta['factor'] === 1) {
                $baseUnit = $u;
                break;
            }
        }

        if (!$baseUnit) {
            return [$qty, $unit];
        }

        return [$qty * $factor, $baseUnit];
    }

    /**
     * Konversi qty dari satu satuan ke satuan lain (harus dimensi sama).
     * Contoh: convert(0.5, 'kg', 'gram') → 500
     * Kalau beda dimensi (ml → gram), kembalikan qty asli.
     */
    public static function convert(float $qty, string $from, string $to): float
    {
        $infoFrom = self::info($from);
        $infoTo   = self::info($to);

        // Satuan sama persis, tidak perlu konversi
        if (self::normalise($from) === self::normalise($to)) {
            return $qty;
        }

        if (!$infoFrom || !$infoTo) {
            return $qty; // satuan tidak dikenal
        }

        if ($infoFrom['dim'] !== $infoTo['dim']) {
            return $qty; // beda dimensi, tidak bisa dikonversi
        }

        [$qtyBase] = self::toBase($qty, $from);
        $toFactor  = $infoTo['factor'];

        return $toFactor > 0 ? ($qtyBase / $toFactor) : $qty;
    }

    /**
     * Kembalikan true jika dua satuan bisa dikonversi satu sama lain.
     */
    public static function isCompatible(string $from, string $to): bool
    {
        $a = self::info($from);
        $b = self::info($to);

        return $a && $b && ($a['dim'] === $b['dim']);
    }

    /**
     * Kembalikan dimensi satuan ('weight', 'volume', 'count', 'portion', 'pack')
     * atau null kalau tidak dikenal.
     */
    public static function getDimension(string $unit): ?string
    {
        return self::info($unit)['dim'] ?? null;
    }
}
