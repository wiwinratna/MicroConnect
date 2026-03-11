<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ======================= SCHEDULE MINECT =======================

// Generate tagihan iuran bulanan: jalan tiap tanggal 1 jam 08:00
Schedule::command('iuran:generate')->monthlyOn(1, '08:00');

// Kirim reminder WA piutang: jalan tiap hari jam 09:00
Schedule::command('piutang:reminder')->dailyAt('09:00');

