<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';

    protected $fillable = [
        'umkm_id',
        'bahan_id',
        'produk_id',
        'tanggal',
        'jenis',
        'qty',
        'harga_unit',
        'ref_tipe',
        'ref_id',
        'ref_detail_id',
    ];
}

