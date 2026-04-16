<?php

namespace App\Http\Middleware;

use App\Helpers\FeatureAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware: CheckUmkmFeature
 *
 * Proteksi backend route berdasarkan level akses UMKM.
 *
 * Cara pakai di route:
 *   ->middleware('umkm.feature:buku_besar')
 *   ->middleware('umkm.feature:buku_besar,laba_rugi')  // AND — semua harus bisa
 *
 * Jika akses ditolak:
 *   - Request JSON/AJAX  → abort 403 JSON
 *   - Request biasa      → redirect ke dashboard dengan pesan error
 */
class CheckUmkmFeature
{
    public function handle(Request $request, Closure $next, string ...$features): mixed
    {
        foreach ($features as $feature) {
            if (!FeatureAccess::can($feature)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Fitur ini tidak tersedia untuk level UMKM Anda.',
                        'feature' => $feature,
                    ], 403);
                }

                return redirect()
                    ->route('umkm.dashboard')
                    ->with('error', 'Fitur <strong>' . $feature . '</strong> belum tersedia untuk level UMKM Anda. Hubungi admin untuk upgrade level.');
            }
        }

        return $next($request);
    }
}
