<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BR-07: status akun UMKM diperiksa di setiap permintaan, bukan hanya saat login.
 * Akun yang dinonaktifkan langsung dikeluarkan, termasuk sesi "Ingat saya".
 */
class AkunAktif
{
    private const PESAN = 'Akun Anda sedang nonaktif. Hubungi Admin KADIN.';

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $umkm = $user && $user->user_group !== 'admin' ? $user->umkm : null;

        if (!$umkm || $umkm->status === 'aktif') {
            return $next($request);
        }

        // logout() sekaligus mengganti token "Ingat saya".
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => self::PESAN], 403);
        }

        return redirect()->route('umkm.login')->withErrors(['email' => self::PESAN]);
    }
}
