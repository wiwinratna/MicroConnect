<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * BR-07: pengguna masuk memakai ID login (username diikuti ".id") atau email.
 * Isian yang mengandung "@" selalu dibaca sebagai email.
 */
final class PengenalLogin
{
    public const AKHIRAN = '.id';

    public static function dariUsername(?string $username): ?string
    {
        return filled($username) ? $username . self::AKHIRAN : null;
    }

    /** @return array<string,string> kolom pencarian pengguna, tanpa kata sandi */
    public static function kredensial(string $isian): array
    {
        $isian = Str::lower(trim($isian));

        if (!str_contains($isian, '@') && str_ends_with($isian, self::AKHIRAN)) {
            return ['username' => Str::beforeLast($isian, self::AKHIRAN)];
        }

        return ['email' => $isian];
    }

    /** Kunci batas percobaan: ID dan email milik satu akun dihitung bersama. */
    public static function kunci(string $isian): string
    {
        $kredensial = self::kredensial($isian);
        $id = User::where($kredensial)->value('id');

        return $id ? 'user:' . $id : implode('', $kredensial);
    }
}
