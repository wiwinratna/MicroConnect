<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IuranBulanan extends Model
{
    protected $table = 'iuran_bulanan';

    protected $fillable = [
        'iuran_periode_id',
        'umkm_id',
        'periode',
        'nominal',
        'status',
        'jatuh_tempo',
        'midtrans_order_id',
        'midtrans_snap_token',
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function iuranPeriode()
    {
        return $this->belongsTo(IuranPeriode::class, 'iuran_periode_id');
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

    /**
     * Cek apakah tagihan sudah melewati jatuh tempo dan belum lunas.
     * Label "terlambat" dihitung secara dinamis, bukan status database.
     */
    public function isTerlambat(): bool
    {
        return !$this->isLunas()
            && $this->jatuh_tempo
            && now()->gt($this->jatuh_tempo);
    }

    /**
     * Apakah tagihan bisa dibayar (belum lunas, belum expire/cancel/deny).
     */
    public function isBayarable(): bool
    {
        return in_array($this->status, ['belum_bayar', 'pending']);
    }

    public function markLunas(): void
    {
        $this->update([
            'status'       => 'lunas',
            'dibayar_pada' => now(),
        ]);
    }

    /**
     * Label status yang ramah untuk tampilan.
     */
    public function statusLabel(): string
    {
        if ($this->isTerlambat()) {
            return 'Terlambat';
        }

        return match ($this->status) {
            'lunas'       => 'Lunas',
            'pending'     => 'Menunggu Pembayaran',
            'expire'      => 'Kedaluwarsa',
            'cancel'      => 'Dibatalkan',
            'deny'        => 'Ditolak',
            default       => 'Belum Bayar',
        };
    }

    /**
     * Warna badge untuk tampilan.
     */
    public function statusBadgeClass(): string
    {
        if ($this->isTerlambat()) {
            return 'bg-danger';
        }

        return match ($this->status) {
            'lunas'       => 'bg-success',
            'pending'     => 'bg-info',
            'expire'      => 'bg-secondary',
            'cancel'      => 'bg-secondary',
            'deny'        => 'bg-danger',
            default       => 'bg-warning text-dark',
        };
    }
}
