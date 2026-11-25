<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Data calon UMKM sebelum akun ada (BR-06, BR-13). Tanpa kata sandi. */
class PengajuanPendaftaran extends Model
{
    protected $table = 'pengajuan_pendaftaran';

    protected $fillable = [
        'umkm_id', 'nama_pemilik', 'email', 'username', 'no_wa',
        'nama_usaha', 'alamat', 'no_telepon',
        'jenis_usaha_id', 'rumpun', 'memiliki_persediaan', 'melakukan_produksi',
        'persetujuan_data_pribadi', 'tanggal_persetujuan',
        'perlu_keputusan_admin', 'tanggal_pengajuan', 'status',
        'alasan_penolakan', 'diverifikasi_oleh', 'tanggal_verifikasi',
    ];

    protected $casts = [
        'memiliki_persediaan'      => 'boolean',
        'melakukan_produksi'       => 'boolean',
        'persetujuan_data_pribadi' => 'boolean',
        'perlu_keputusan_admin'    => 'boolean',
        'tanggal_persetujuan'      => 'datetime',
        'tanggal_pengajuan'        => 'datetime',
        'tanggal_verifikasi'       => 'datetime',
    ];

    public function penilaian(): HasOne
    {
        return $this->hasOne(PenilaianLevel::class, 'pengajuan_id');
    }

    public function jenisUsaha(): BelongsTo
    {
        return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id');
    }
}
