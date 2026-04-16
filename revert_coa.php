<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$coas = App\Models\Coa::all();
foreach($coas as $c) {
    if(!is_numeric($c->header_akun)) {
        $h = 1;
        if($c->header_akun == 'Kewajiban') $h = 2;
        elseif($c->header_akun == 'Modal') $h = 3;
        elseif($c->header_akun == 'Pendapatan') $h = 4;
        elseif($c->header_akun == 'Beban') $h = 5;
        $c->update(['header_akun' => $h]);
        echo "Reverted $c->kode_akun to $h\n";
    }
}
