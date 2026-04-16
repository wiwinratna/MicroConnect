<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\PelakuUsaha;
use App\Http\Middleware\AdminOnly;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'pelakuusaha'          => \App\Http\Middleware\PelakuUsaha::class,
            'adminonly'            => \App\Http\Middleware\AdminOnly::class,
            'must_change_password' => \App\Http\Middleware\MustChangePassword::class,
            'umkm.feature'         => \App\Http\Middleware\CheckUmkmFeature::class,
        ]);

        // Exclude Midtrans webhook dari CSRF verification
        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
