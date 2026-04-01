<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/umkm/export/jurnal-umum?bulan=2026-03&format=pdf', 'GET');

// Simulate auth
$user = App\Models\User::find(2); // Asumsi ada user ID 2 (UMKM)
if(!$user) {
    echo "User not found, attempting ID 1\n";
    $user = App\Models\User::find(1);
}
auth()->login($user);

$response = $kernel->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() >= 400) {
    echo $response->getContent();
} else {
    echo "Success! PDF generated.\n";
}
