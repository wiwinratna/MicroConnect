<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangPembayaran extends Model
{
    protected $table = 'piutang_pembayaran';

    protected $fillable = [
        'piutang_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar'  => 'decimal:2',
    ];

    // ===================== RELASI =====================

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_id');
    }
}
