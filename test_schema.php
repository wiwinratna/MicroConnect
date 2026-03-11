<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    DB::statement('DROP TABLE IF EXISTS tickets');
    echo "Dropped tickets\n";
    
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->string('kode');
    });
    echo "Created tickets successfully\n";
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
