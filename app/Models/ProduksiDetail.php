<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProduksiDetail extends Model
{
    use HasFactory;

    protected $table = 'produksi_detail';

    protected $fillable = [
        'produksi_id','produk_id','qty_hasil','hpp_per_unit','hpp_total'
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class, 'produksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
