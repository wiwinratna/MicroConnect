<?php

/**
 * Konfigurasi Midtrans Payment Gateway.
 *
 * Untuk pindah dari Sandbox ke Production, cukup ubah di .env:
 * - MIDTRANS_IS_PRODUCTION=true
 * - MIDTRANS_SERVER_KEY=Mid-server-xxxxx (key production)
 * - MIDTRANS_CLIENT_KEY=Mid-client-xxxxx (key production)
 * - MIDTRANS_SNAP_URL=https://app.midtrans.com/snap/snap.js
 */
return [
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_sanitized'  => true,
    'is_3ds'        => true,

    // URL SNAP JS — otomatis gunakan sandbox atau production
    'snap_url'      => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),
];
