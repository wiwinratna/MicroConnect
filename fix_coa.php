<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$coas = App\Models\Coa::all();
foreach($coas as $c) {
    if(is_numeric($c->header_akun)) {
        $h = 'Aset';
        if($c->header_akun == 2) $h = 'Kewajiban';
        elseif($c->header_akun == 3) $h = 'Modal';
        elseif($c->header_akun == 4) $h = 'Pendapatan';
        elseif($c->header_akun == 5) $h = 'Beban';
        $c->update(['header_akun' => $h]);
        echo "Updated $c->kode_akun to $h\n";
    }
}
