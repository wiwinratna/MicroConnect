<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    app()->call('App\Http\Controllers\Admin\DashboardController@index');
    echo "Query runs fine without view syntax error\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
