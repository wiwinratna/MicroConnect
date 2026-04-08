<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware yang memaksa user untuk mengganti password sementara
 * sebelum bisa mengakses halaman lain.
 *
 * Diterapkan pada UMKM yang akunnya dibuat oleh admin.
 */
class MustChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Izinkan akses ke halaman ganti password dan logout
            $allowed = [
                'password.change',
                'password.change.update',
                'logout',
            ];

            if (!in_array($request->route()->getName(), $allowed)) {
                return redirect()->route('password.change')
                    ->with('warning', 'Anda harus mengganti password sementara terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
