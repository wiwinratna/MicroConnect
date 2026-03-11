<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::statement('DROP TABLE IF EXISTS ticket_messages;');
DB::statement('DROP TABLE IF EXISTS tickets;');
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Drop Success\n";

