<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Penilaian kesiapan (awal atau ulang). Level hanya ditetapkan Admin (BR-01). */
class PenilaianLevel extends Model
{
    protected $table = 'penilaian_level';

    protected $fillable = [
        'umkm_id', 'pengajuan_id', 'periode_id', 'jenis', 'tanggal_penilaian',
        'total_skor', 'level_rekomendasi_id', 'arah_rekomendasi',
        'level_akhir_id', 'alasan_penetapan', 'status_peninjauan',
        'catatan_peninjauan', 'ditinjau_oleh', 'tanggal_peninjauan',
        'tindak_lanjut_pembinaan', 'data_sistem',
    ];

    protected $casts = [
        'tanggal_penilaian'  => 'date',
        'tanggal_peninjauan' => 'datetime',
        'data_sistem'        => 'array',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenilaian::class, 'penilaian_id');
    }

    public function levelRekomendasi(): BelongsTo
    {
        return $this->belongsTo(UmkmLevel::class, 'level_rekomendasi_id');
    }

    public function levelAkhir(): BelongsTo
    {
        return $this->belongsTo(UmkmLevel::class, 'level_akhir_id');
    }
}
