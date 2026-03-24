<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'umkm_id',
        'nama_pelanggan',
        'no_whatsapp',
        'email',
        'alamat',
        'catatan',
    ];

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'pelanggan_id');
    }

    // ===================== HELPER =====================

    public function totalPiutangAktif(): float
    {
        return (float) $this->piutang()
            ->whereIn('status', ['belum_lunas', 'sebagian'])
            ->sum('sisa');
    }
}
