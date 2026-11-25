<?php

/**
 * Konfigurasi khusus MINECT v1.1.
 * Nilai dibaca dari .env supaya tidak ada rahasia di kode (BR-08).
 */
return [

    // Kata sandi awal seragam untuk akun yang dibuat dari pengajuan yang disetujui.
    'initial_password' => env('INITIAL_PASSWORD'),

    // Driver notifikasi: 'simulasi' (default) atau 'whatsapp'.
    'notif_driver' => env('NOTIF_DRIVER', 'simulasi'),

];
