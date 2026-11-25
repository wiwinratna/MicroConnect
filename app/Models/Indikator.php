<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Indikator kuesioner (Tabel 3.6): satu pertanyaan dengan beberapa pilihan. */
class Indikator extends Model
{
    protected $table = 'indikator';

    protected $fillable = ['nama', 'definisi', 'pertanyaan', 'sumber_data', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function pilihan(): HasMany
    {
        return $this->hasMany(PilihanJawaban::class, 'indikator_id')->orderBy('urutan');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
