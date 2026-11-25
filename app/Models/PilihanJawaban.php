<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu pilihan jawaban beserta skornya (BR-09, BR-12). */
class PilihanJawaban extends Model
{
    protected $table = 'pilihan_jawaban';

    protected $fillable = [
        'indikator_id', 'teks', 'contoh', 'nilai_min', 'nilai_max',
        'skor', 'di_luar_batas_mikro', 'urutan',
    ];

    protected $casts = ['di_luar_batas_mikro' => 'boolean'];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }
}
