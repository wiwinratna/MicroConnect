<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PelakuUsaha
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403);
        }

        $group = auth()->user()->user_group;

        // terima beberapa variasi yang mungkin keburu kepake di DB
        if (!in_array($group, ['pelakuusaha', 'pelaku_usaha', 'umkm'])) {
            abort(403, 'Anda tidak memiliki akses sebagai pelaku usaha.');
        }

        return $next($request);
    }
}
