<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jawaban per indikator; teks dan skor disalin saat dijawab (snapshot). */
class DetailPenilaian extends Model
{
    protected $table = 'detail_penilaian';

    protected $fillable = ['penilaian_id', 'indikator_id', 'pilihan_id', 'teks_pilihan', 'skor'];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }
}
