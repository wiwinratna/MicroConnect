<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Periode penilaian ulang yang dibuka Admin (FR-21). */
class PeriodePenilaian extends Model
{
    protected $table = 'periode_penilaian';

    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'status', 'dibuat_oleh'];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function penilaian(): HasMany
    {
        return $this->hasMany(PenilaianLevel::class, 'periode_id');
    }

    /** Berlaku bila status dibuka dan hari ini berada di antara tanggal mulai dan selesai. */
    public function berlaku(): bool
    {
        $hariIni = now()->startOfDay();

        return $this->status === 'dibuka'
            && $this->tanggal_mulai->startOfDay()->lte($hariIni)
            && $this->tanggal_selesai->startOfDay()->gte($hariIni);
    }
}
