<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produksi extends Model
{
    use HasFactory;

    protected $table = 'produksi';

    protected $fillable = ['umkm_id', 'kode_produksi', 'tanggal', 'catatan'];

    public function details()
    {
        return $this->hasMany(ProduksiDetail::class, 'produksi_id');
    }

    public static function generateKode(): string
    {
        $last = self::orderByDesc('id')->first();
        $num = $last ? ((int) preg_replace('/\D/', '', $last->kode_produksi)) : 0;
        $next = $num + 1;
        return 'PRD-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
