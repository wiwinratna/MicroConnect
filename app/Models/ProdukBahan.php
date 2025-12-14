<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukBahan extends Model
{
    protected $table = 'produk_bahan';

    protected $fillable = [
        'produk_id',
        'bahan_id',
        'qty',
        'satuan',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function bahan()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_id');
    }
    
}
