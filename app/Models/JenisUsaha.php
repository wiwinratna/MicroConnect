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

    /** Nama tanpa contoh dalam kurung, muat di kolom umkm.jenis_usaha (maks. 50 karakter). */
    public function namaPendek(): string
    {
        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s*\(mis\..*$/u', '', $this->nama)), 50, '');
    }
}
