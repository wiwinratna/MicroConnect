<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirect logged-in users to their dashboard based on their role.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect berdasarkan role
                if ($user->user_group === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($user->user_group === 'pelakuusaha') {
                    return redirect()->route('umkm.dashboard');
                }

                // Fallback
                return redirect('/');
            }
        }

        return $next($request);
    }
}
