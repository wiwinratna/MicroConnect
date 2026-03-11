<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiLog extends Model
{
    protected $table = 'notifikasi_log';

    protected $fillable = [
        'umkm_id',
        'tipe',
        'tujuan',
        'pesan',
        'status',
        'response',
        'notifiable_id',
        'notifiable_type',
        'dikirim_pada',
    ];

    protected $casts = [
        'dikirim_pada' => 'datetime',
    ];

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    /**
     * Polymorphic: bisa merujuk ke Piutang atau IuranBulanan.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
