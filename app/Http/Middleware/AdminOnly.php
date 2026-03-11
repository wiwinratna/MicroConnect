<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->user_group !== 'admin') {
            return redirect()->route('admin.login');
        }
        return $next($request);
    }
}

