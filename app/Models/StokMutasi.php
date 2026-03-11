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
    public function bahan()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
