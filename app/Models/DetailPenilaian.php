<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jawaban per indikator; teks dan skor disalin saat dijawab (snapshot). */
class DetailPenilaian extends Model
{
    protected $table = 'detail_penilaian';

    protected $fillable = ['penilaian_id', 'indikator_id', 'pilihan_id', 'teks_pilihan', 'skor'];
}
