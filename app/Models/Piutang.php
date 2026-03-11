<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $table = 'piutang';

    protected $fillable = [
        'umkm_id',
        'pelanggan_id',
        'penjualan_id',
        'kode_piutang',
        'tanggal',
        'jatuh_tempo',
        'nominal_awal',
        'sudah_dibayar',
        'sisa',
        'status',
        'catatan',
        'reminder_h3_terkirim',
        'reminder_h0_terkirim',
    ];

    protected $casts = [
        'tanggal'              => 'date',
        'jatuh_tempo'          => 'date',
        'nominal_awal'         => 'decimal:2',
        'sudah_dibayar'        => 'decimal:2',
        'sisa'                 => 'decimal:2',
        'reminder_h3_terkirim' => 'boolean',
        'reminder_h0_terkirim' => 'boolean',
    ];

    // ===================== SCOPES =====================

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['belum_lunas', 'sebagian']);
    }

    public function scopeJatuhTempoHari($query, int $hari)
    {
        return $query->whereDate('jatuh_tempo', now()->addDays($hari)->toDateString());
    }

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(PiutangPembayaran::class, 'piutang_id');
    }

    public function notifikasiLog()
    {
        return $this->morphMany(NotifikasiLog::class, 'notifiable');
    }

    // ===================== HELPERS =====================

    /**
     * Tambahkan pembayaran, update sudah_dibayar, sisa, dan status.
     */
    public function catatPembayaran(float $jumlah, string $tanggal, string $metode = null, string $catatan = null): PiutangPembayaran
    {
        $bayar = PiutangPembayaran::create([
            'piutang_id'   => $this->id,
            'tanggal_bayar' => $tanggal,
            'jumlah_bayar' => $jumlah,
            'metode_bayar' => $metode,
            'catatan'      => $catatan,
        ]);

        $sudahBayar = $this->sudah_dibayar + $jumlah;
        $sisa       = max(0, $this->nominal_awal - $sudahBayar);
        $status     = $sisa <= 0 ? 'lunas'
                    : ($sudahBayar > 0 ? 'sebagian' : 'belum_lunas');

        $this->update([
            'sudah_dibayar' => $sudahBayar,
            'sisa'          => $sisa,
            'status'        => $status,
        ]);

        return $bayar;
    }

    public static function generateKode(): string
    {
        $last = self::orderByDesc('id')->first();
        $num  = $last ? ((int) substr($last->kode_piutang, -5) + 1) : 1;
        return 'PT-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
