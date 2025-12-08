<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PelakuUsaha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Harus sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Hanya user_group = 'umkm' yang boleh lanjut
        if (auth()->user()->user_group === 'umkm') {
            return $next($request);
        }

        // Selain itu dilarang
        abort(403, 'Anda tidak memiliki akses sebagai pelaku usaha.');
    }
}
