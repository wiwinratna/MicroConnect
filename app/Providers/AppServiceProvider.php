<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // v1.1: driver notifikasi dipilih lewat NOTIF_DRIVER (simulasi | whatsapp).
        $this->app->bind(
            \App\Services\Notifikasi\NotifikasiDriver::class,
            fn () => config('minect.notif_driver') === 'whatsapp'
                ? new \App\Services\Notifikasi\WhatsappDriver()
                : new \App\Services\Notifikasi\SimulasiDriver()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Batas percobaan login: 5 per menit untuk satu email dari satu alamat, dan 20 per menit
        // per alamat agar satu kata sandi tidak bisa dicobakan ke banyak email.
        RateLimiter::for('login', function (Request $request) {
            $tolak = fn (Request $r, array $headers) => back()
                ->withErrors(['email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam ' . ($headers['Retry-After'] ?? 60) . ' detik.'])
                ->onlyInput('email');
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(5)->by('login:' . $email . '|' . $request->ip())->response($tolak),
                Limit::perMinute(20)->by('login-ip:' . $request->ip())->response($tolak),
            ];
        });
    }
}
