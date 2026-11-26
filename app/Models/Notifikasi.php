<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu notifikasi. Tiap kejadian menulis dua baris: saluran whatsapp (dikirim lewat driver)
 * dan saluran aplikasi (salinan untuk Kotak Notifikasi). Tabel ini terpisah dari
 * notifikasi_log v1.0 karena calon UMKM belum punya baris umkm.
 */
class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'umkm_id', 'pengajuan_id', 'no_tujuan', 'jenis', 'saluran',
        'isi', 'status', 'percobaan', 'dikirim_pada', 'pesan_galat',
    ];

    protected $casts = [
        'dikirim_pada' => 'datetime',
    ];

    /** Isi untuk layar Admin: kata sandi awal disamarkan. Pesan tersimpan tidak diubah. */
    public function isiTersamar(): string
    {
        return preg_replace('/(Kata sandi awal: ).*?(\. Silakan|$)/u', '$1••••••••$2', (string) $this->isi);
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }
}
