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
        'saldo_sebelum',
        'saldo_sesudah',
        'jatuh_tempo_baru',
        'is_pelunasan',
        'metode_bayar',
        'catatan',
    ];

    protected $casts = [
        'tanggal_bayar'    => 'date',
        'jumlah_bayar'     => 'decimal:2',
        'saldo_sebelum'    => 'decimal:2',
        'saldo_sesudah'    => 'decimal:2',
        'jatuh_tempo_baru' => 'date',
        'is_pelunasan'     => 'boolean',
    ];

    // ===================== RELASI =====================

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_id');
    }
}
