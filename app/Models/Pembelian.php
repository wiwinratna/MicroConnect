<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';

    protected $fillable = [
        'umkm_id',
        'kode_pembelian',
        'nomor_nota',
        'tanggal',
        'supplier',
        'catatan',
        'total',
    ];

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }

    public static function generateKode(): string
    {
        $last = self::orderByDesc('id')->first();
        $lastNum = $last ? (int) substr($last->kode_pembelian, -5) : 0;

        return 'PB-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
    }
}
