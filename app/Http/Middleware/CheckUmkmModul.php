<?php

namespace App\Http\Middleware;

use App\Helpers\ModulAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Blokir akses ke modul yang tidak berlaku untuk jenis usaha UMKM (FR-19).
 * Dipakai di rute, bukan hanya menyembunyikan menu.
 *
 * Contoh: ->middleware('umkm.modul:bahan_baku')
 */
class CheckUmkmModul
{
    public function handle(Request $request, Closure $next, string ...$moduls): mixed
    {
        foreach ($moduls as $modul) {
            if (!ModulAccess::boleh($modul)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Fitur ini tidak tersedia untuk jenis usaha atau level Anda.',
                        'modul'   => $modul,
                    ], 403);
                }

                return redirect()
                    ->route('umkm.dashboard')
                    ->with('error', 'Fitur ini tidak tersedia untuk jenis usaha atau level Anda.');
            }
        }

        return $next($request);
    }
}
