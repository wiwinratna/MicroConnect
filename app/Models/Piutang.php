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
        'reminder_send_time',
        'email_reminder_enabled',
        'last_email_reminder_sent_at',
        'email_reminder_count',
    ];

    protected $casts = [
        'tanggal'                       => 'date',
        'jatuh_tempo'                   => 'date',
        'nominal_awal'                  => 'decimal:2',
        'sudah_dibayar'                 => 'decimal:2',
        'sisa'                          => 'decimal:2',
        'reminder_h3_terkirim'          => 'boolean',
        'reminder_h0_terkirim'          => 'boolean',
        'email_reminder_enabled'        => 'boolean',
        'last_email_reminder_sent_at'   => 'datetime',
        'email_reminder_count'          => 'integer',
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
     * Tambahkan pembayaran, update sudah_dibayar, sisa, status, dan jatuh_tempo.
     *
     * @param float       $jumlah          Nominal yang dibayar saat ini
     * @param string      $tanggal         Tanggal pembayaran
     * @param string|null $metode          Metode bayar (opsional)
     * @param string|null $catatan         Catatan (opsional)
     * @param string|null $jatuhTempoBaru  Jatuh tempo baru untuk sisa piutang (wajib jika parsial)
     */
    public function catatPembayaran(
        float   $jumlah,
        string  $tanggal,
        ?string $metode = null,
        ?string $catatan = null,
        ?string $jatuhTempoBaru = null
    ): PiutangPembayaran {
        $saldoSebelum = (float) $this->sisa;
        $sudahBayar   = (float) $this->sudah_dibayar + $jumlah;
        $saldoSesudah = max(0, (float) $this->nominal_awal - $sudahBayar);
        $isPelunasan  = $saldoSesudah <= 0;
        $status       = $isPelunasan ? 'lunas'
                      : ($sudahBayar > 0 ? 'sebagian' : 'belum_lunas');

        // Simpan record pembayaran dengan histori saldo
        $bayar = PiutangPembayaran::create([
            'piutang_id'       => $this->id,
            'tanggal_bayar'    => $tanggal,
            'jumlah_bayar'     => $jumlah,
            'saldo_sebelum'    => $saldoSebelum,
            'saldo_sesudah'    => $saldoSesudah,
            'jatuh_tempo_baru' => $isPelunasan ? null : $jatuhTempoBaru,
            'is_pelunasan'     => $isPelunasan,
            'metode_bayar'     => $metode,
            'catatan'          => $catatan,
        ]);

        // Update piutang utama
        $updateData = [
            'sudah_dibayar' => $sudahBayar,
            'sisa'          => $saldoSesudah,
            'status'        => $status,
        ];

        // Jika pelunasan, catat tanggal lunas
        if ($isPelunasan) {
            $updateData['catatan'] = trim(($this->catatan ?? '') . "\n[Lunas pada {$tanggal}]");
        }

        // Jika parsial dan ada jatuh tempo baru, update jatuh tempo piutang
        if (!$isPelunasan && $jatuhTempoBaru) {
            $updateData['jatuh_tempo'] = $jatuhTempoBaru;
        }

        $this->update($updateData);

        return $bayar;
    }

    public static function generateKode(): string
    {
        $last = self::orderByDesc('id')->first();
        $num  = $last ? ((int) substr($last->kode_piutang, -5) + 1) : 1;
        return 'PT-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
