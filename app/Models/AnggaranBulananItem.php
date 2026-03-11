<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnggaranBulananItem extends Model
{
    use HasFactory;

    protected $table = 'anggaran_bulanan_item';

    protected $fillable = ['anggaran_id', 'nama_biaya', 'nominal'];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function anggaran()
    {
        return $this->belongsTo(AnggaranBulanan::class, 'anggaran_id');
    }
}
