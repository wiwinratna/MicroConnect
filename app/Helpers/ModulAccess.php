<?php

namespace App\Helpers;

use App\Models\Umkm;
use Illuminate\Support\Facades\DB;

/**
 * ModulAccess — menentukan apakah UMKM boleh memakai sebuah modul operasional
 * berdasarkan rumpun, kepemilikan persediaan, dan aktivitas produksi (Tabel 3.8),
 * ditambah batas level (FeatureAccess). Aturan dibaca dari tabel aturan_modul,
 * bukan if-else di kode (FR-17, FR-19).
 *
 * BR-14: UMKM lama yang rumpunnya masih kosong, atau yang flag-nya belum diisi,
 * tidak disembunyikan fitur apa pun.
 */
class ModulAccess
{
    /** Modul yang juga dibatasi level: kode modul => kunci fitur di FeatureAccess. */
    public const KUNCI_LEVEL = [
        'bahan_baku' => 'bahan_baku',
        'kartu_stok' => 'kartu_stok',
        'produksi'   => 'produksi',
        'produk'     => 'produk',
        'pembelian'  => 'pembelian',
        'penjualan'  => 'penjualan',
        'beban'      => 'beban',
    ];

    public static function boleh(string $modul, ?Umkm $umkm = null): bool
    {
        $user = auth()->user();

        // Admin tidak dibatasi aturan modul UMKM.
        if ($user && $user->user_group === 'admin') {
            return true;
        }

        $umkm ??= $user?->umkm;

        if (!static::lolosAturan($modul, $umkm)) {
            return false;
        }

        $kunci = self::KUNCI_LEVEL[$modul] ?? null;

        return $kunci === null || FeatureAccess::can($kunci);
    }

    /** Label tampilan modul (mis. "Produk" atau "Daftar Layanan Jasa"); $bawaan jika tidak ada. */
    public static function label(string $modul, string $bawaan, ?Umkm $umkm = null): string
    {
        $umkm ??= auth()->user()?->umkm;
        $aturan = static::aturan($modul, $umkm);

        return filled($aturan?->label_tampil) ? $aturan->label_tampil : $bawaan;
    }

    private static function lolosAturan(string $modul, ?Umkm $umkm): bool
    {
        $aturan = static::aturan($modul, $umkm);

        // Rumpun kosong atau tidak ada aturan: tidak ada yang disembunyikan (BR-14).
        if ($aturan === null) {
            return true;
        }

        return match ($aturan->kondisi) {
            'tidak_ditampilkan'        => false,
            'jika_memiliki_persediaan' => static::flag($umkm->memiliki_persediaan),
            'jika_melakukan_produksi'  => static::flag($umkm->melakukan_produksi),
            default                    => true, // 'selalu' dan 'sesuai_level' (level dicek terpisah)
        };
    }

    /** Flag kosong (null) berarti belum dilengkapi Admin: jangan sembunyikan. */
    private static function flag(mixed $nilai): bool
    {
        return $nilai === null ? true : (bool) $nilai;
    }

    private static function aturan(string $modul, ?Umkm $umkm): ?object
    {
        if (!$umkm || !$umkm->rumpun) {
            return null;
        }

        return DB::table('aturan_modul')
            ->join('modul', 'modul.id', '=', 'aturan_modul.modul_id')
            ->where('modul.kode', $modul)
            ->where('aturan_modul.rumpun', $umkm->rumpun)
            ->first(['aturan_modul.kondisi', 'aturan_modul.label_tampil']);
    }
}
