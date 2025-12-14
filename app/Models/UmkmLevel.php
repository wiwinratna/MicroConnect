<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmkmLevel extends Model
{
    use HasFactory;
    protected $table = 'umkm_level';

    protected $fillable = [
        'kode',
        'nama_level',
        'deskripsi',
        'fitur',
        'iuran_bulanan',
    ];

    protected $casts = [
        'fitur' => 'array',   // biar fitur otomatis jadi array di PHP
    ];
}
