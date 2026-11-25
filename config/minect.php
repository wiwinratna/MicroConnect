<?php

/**
 * Konfigurasi khusus MINECT v1.1.
 * Nilai dibaca dari .env supaya tidak ada rahasia di kode (BR-08).
 */
return [

    // Kata sandi awal seragam untuk akun yang dibuat dari pengajuan yang disetujui.
    'initial_password' => env('INITIAL_PASSWORD'),

    // Apakah akun baru dari pengajuan yang disetujui WAJIB mengganti kata sandi awal saat
    // login pertama (BR-08). Bawaan: tidak wajib (pemilik boleh memilih sendiri).
    // Isi FORCE_CHANGE_INITIAL_PASSWORD=true di .env untuk mengembalikan aturan wajib ganti.
    'force_change_initial_password' => filter_var(env('FORCE_CHANGE_INITIAL_PASSWORD', false), FILTER_VALIDATE_BOOLEAN),

    // Driver notifikasi: 'simulasi' (default) atau 'whatsapp'.
    'notif_driver' => env('NOTIF_DRIVER', 'simulasi'),

];
