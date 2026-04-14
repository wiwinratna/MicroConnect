<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'umkm_id',
        'nama_pelanggan',
        'nama_instansi',
        'no_ktp',
        'foto_ktp',
        'no_whatsapp',
        'kontak_alternatif',
        'email',
        'alamat',
        'nama_pic',
        'catatan',
        'batas_maksimal_piutang',
        'jatuh_tempo_default',
        'catatan_penagihan',
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
