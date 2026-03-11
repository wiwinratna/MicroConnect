<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IuranBulanan extends Model
{
    protected $table = 'iuran_bulanan';

    protected $fillable = [
        'umkm_id',
        'periode',
        'nominal',
        'status',
        'jatuh_tempo',
        'midtrans_order_id',
        'midtrans_payment_url',
        'dibayar_pada',
    ];

    protected $casts = [
        'jatuh_tempo'  => 'date',
        'dibayar_pada' => 'datetime',
        'nominal'      => 'decimal:2',
    ];

    // ===================== SCOPES =====================

    public function scopeBelumBayar($query)
    {
        return $query->where('status', 'belum_bayar');
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function notifikasiLog()
    {
        return $this->morphMany(NotifikasiLog::class, 'notifiable');
    }

    // ===================== HELPER =====================

    public function isLunas(): bool
    {
        return $this->status === 'lunas';
    }

    public function markLunas(): void
    {
        $this->update([
            'status'      => 'lunas',
            'dibayar_pada' => now(),
        ]);
    }
}
