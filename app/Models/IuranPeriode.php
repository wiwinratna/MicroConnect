<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IuranPeriode extends Model
{
    protected $table = 'iuran_periode';

    protected $fillable = [
        'periode',
        'nominal_default',
        'jatuh_tempo',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'jatuh_tempo'      => 'date',
        'nominal_default'  => 'decimal:2',
    ];

    // ===================== SCOPES =====================

    public function scopeTerbit($query)
    {
        return $query->where('status', 'terbit');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // ===================== RELASI =====================

    public function iuranBulanan()
    {
        return $this->hasMany(IuranBulanan::class, 'iuran_periode_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ===================== HELPERS =====================

    /**
     * Total UMKM yang terkait periode ini.
     */
    public function totalUmkm(): int
    {
        return $this->iuranBulanan()->count();
    }

    /**
     * Jumlah UMKM yang sudah lunas.
     */
    public function totalLunas(): int
    {
        return $this->iuranBulanan()->where('status', 'lunas')->count();
    }

    /**
     * Jumlah UMKM yang belum bayar (termasuk pending).
     */
    public function totalBelumBayar(): int
    {
        return $this->iuranBulanan()->whereIn('status', ['belum_bayar', 'pending'])->count();
    }

    /**
     * Format periode menjadi teks yang ramah.
     * "2026-03" → "Maret 2026"
     */
    public function periodeFormatted(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->periode)->translatedFormat('F Y');
    }
}
