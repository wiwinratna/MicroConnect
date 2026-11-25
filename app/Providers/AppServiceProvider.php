<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;

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
    }
}
