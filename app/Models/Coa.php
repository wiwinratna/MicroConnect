<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $table = 'coa';

    protected $fillable = [
        'header_akun',
        'kode_akun',
        'nama_akun',
        'posisi_dr_cr',
        'umkm_id',
    ];
}
