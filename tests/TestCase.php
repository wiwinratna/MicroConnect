<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Pengaman: tes yang memakai RefreshDatabase menghapus seluruh tabel.
     * Hentikan sebelum itu terjadi bila koneksi bukan DB uji `minnect_test`.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $default  = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$default}.database");

        if ($default !== 'mysql' || $database !== 'minnect_test') {
            throw new RuntimeException(
                "Tes dihentikan: koneksi '{$default}' ke DB '{$database}', seharusnya mysql/minnect_test."
            );
        }

        return $app;
    }
}
