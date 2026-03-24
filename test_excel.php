<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/umkm/export/jurnal-umum?bulan=2026-03&format=excel', 'GET');

$user = App\Models\User::find(2);
if(!$user) {
    echo "User not found, attempting ID 1\n";
    $user = App\Models\User::find(1);
}
auth()->login($user);

try {
    $response = $kernel->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo substr($response->getContent(), 0, 1000);
    } else {
        echo "Success! Excel generated.\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
