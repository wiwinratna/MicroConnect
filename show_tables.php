<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo "Current tables:\n";
foreach($tables as $table) {
    $vals = array_values((array)$table);
    echo $vals[0] . "\n";
}

// Ensure both are dropped
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::statement('DROP TABLE IF EXISTS ticket_messages;');
DB::statement('DROP TABLE IF EXISTS tickets;');
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\nTables dropped again.\n";

$migrations = DB::table('migrations')->get();
echo "\nMigrations in DB:\n";
foreach($migrations as $m) {
    echo $m->migration . "\n";
}

DB::table('migrations')->where('migration', 'like', '%ticket%')->delete();
echo "\nDeleted ticket migrations from DB.\n";
