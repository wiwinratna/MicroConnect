<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Umkm extends Model
{
    use HasFactory;
    protected $table = 'umkm';

    protected $fillable = [
        'kode_umkm',
        'user_id',
        'level_id',
        'nama_usaha',
        'nib',
        'alamat',
        'no_telepon',
    ];

    public static function getKodeUmkm()
    {
        // ambil UMKM terakhir berdasarkan created_at
        $last = self::orderByDesc('created_at')->first();

        // kalau belum ada satupun → mulai dari 0
        $lastNumber = $last ? (int) substr($last->kode_umkm, -5) : 0;

        $nextNumber = $lastNumber + 1;

        // hasil: UM00001, UM00002, dst.
        return 'UM' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(UmkmLevel::class, 'level_id');
    }

    public function bahanBaku()
    {
        return $this->hasMany(BahanBaku::class, 'umkm_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'umkm_id');
    }

}
