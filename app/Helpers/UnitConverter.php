<?php

namespace App\Helpers;

class UnitConverter
{
    /**
     * Daftar satuan + faktor ke satuan dasar per dimensi.
     * Base:
     *   - volume: ml
     *   - weight: gram
     *   - count: pcs
     */
    protected static array $units = [
        // VOLUME (base: ml)
        'ml'    => ['dim' => 'volume', 'factor' => 1],
        'milliliter' => ['dim' => 'volume', 'factor' => 1],
        'l'     => ['dim' => 'volume', 'factor' => 1000],
        'liter' => ['dim' => 'volume', 'factor' => 1000],

        // BERAT (base: gram)
        'g'     => ['dim' => 'weight', 'factor' => 1],
        'gram'  => ['dim' => 'weight', 'factor' => 1],
        'kg'    => ['dim' => 'weight', 'factor' => 1000],
        'kilogram' => ['dim' => 'weight', 'factor' => 1000],

        // JUMLAH (base: pcs)
        'pcs'   => ['dim' => 'count', 'factor' => 1],
        'buah'  => ['dim' => 'count', 'factor' => 1],
        'lembar'=> ['dim' => 'count', 'factor' => 1],
        'lusin' => ['dim' => 'count', 'factor' => 12],
        'rim'   => ['dim' => 'count', 'factor' => 500],

        // kalau mau, nanti kamu bisa nambah sendiri di sini
    ];

    protected static function normalise(string $unit): ?string
    {
        $u = strtolower(trim($unit));

        // beberapa alias santai
        if ($u === 'ltr') $u = 'liter';

        return $u !== '' ? $u : null;
    }

    /**
     * Kembalikan dimensi & faktor satuan
     */
    protected static function info(string $unit): ?array
    {
        $u = self::normalise($unit);

        return $u && isset(self::$units[$u]) ? self::$units[$u] : null;
    }

    /**
     * Convert jumlah dari satu satuan ke satuan dasar dimensi itu
     * (ml/gram/pcs).
     *
     * return [qty_dasar, unit_dasar]
     */
    public static function toBase(float $qty, string $unit): array
    {
        $info = self::info($unit);

        if (!$info) {
            // kalau ga dikenal, balikin apa adanya
            return [$qty, $unit];
        }

        $dim    = $info['dim'];
        $factor = $info['factor'];

        // cari unit dasar untuk dimensi ini
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

        $qtyBase = $qty * $factor;

        return [$qtyBase, $baseUnit];
    }

    /**
     * Convert qty dari satu unit ke unit lain (kalau sama dimensi).
     * Contoh: 0.24 liter → 240 ml
     */
    public static function convert(float $qty, string $from, string $to): float
    {
        $infoFrom = self::info($from);
        $infoTo   = self::info($to);

        if (!$infoFrom || !$infoTo) {
            // kalau ga dikenal salah satu, jangan diapa2in
            return $qty;
        }

        if ($infoFrom['dim'] !== $infoTo['dim']) {
            // beda dimensi (ml → gram) = ga logis, balikin aja
            return $qty;
        }

        // ke base dulu
        [$qtyBase, $baseUnit] = self::toBase($qty, $from);

        // sekarang dari base ke target
        $toFactor = $infoTo['factor']; // 1 utk base, >1 utk unit besar (kg, liter)
        if ($toFactor == 0) {
            return $qty;
        }

        // karena factor selalu "berapa ml/gram/pcs dalam 1 unit ini"
        // maka: qty_target = qty_base / factor_target
        return $qtyBase / $toFactor;
    }
}
