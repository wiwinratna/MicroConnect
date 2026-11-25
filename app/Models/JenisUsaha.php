<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jenis usaha (Tabel 3.7) beserta rumpun bawaannya. */
class JenisUsaha extends Model
{
    protected $table = 'jenis_usaha';

    protected $fillable = ['nama', 'rumpun_bawaan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
