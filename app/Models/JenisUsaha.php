<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kategori usaha (tabel jenis_usaha, Tabel 3.7). Kolom rumpun_bawaan menyatakan jenis
 * usaha tempat kategori muncul: barang, jasa, atau keduanya (ditentukan_umkm).
 */
class JenisUsaha extends Model
{
    public const UNTUK = [
        'barang'          => 'Barang',
        'jasa'            => 'Jasa',
        'ditentukan_umkm' => 'Barang dan Jasa',
    ];

    protected $table = 'jenis_usaha';

    protected $fillable = ['nama', 'rumpun_bawaan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /** Apakah kategori ini boleh dipilih untuk jenis usaha barang/jasa. */
    public function cocokDengan(?string $jenis): bool
    {
        return in_array($this->rumpun_bawaan, [$jenis, 'ditentukan_umkm'], true);
    }

    /** Nama tanpa contoh dalam kurung, muat di kolom umkm.jenis_usaha (maks. 50 karakter). */
    public function namaPendek(): string
    {
        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s*\(mis\..*$/u', '', $this->nama)), 50, '');
    }
}
