<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Umkm extends Model
{
    use HasFactory;
    protected $table = 'umkm';

    protected $fillable = [
        'kode_umkm',
        'user_id',
        'level_id',
        'nama_usaha',
        'nib',
        'alamat',
        'no_telepon',
        // Fields baru
        'jenis_usaha',
        'no_whatsapp',
        'recording_method',
        'inventory_method',
        'status',
    ];

    protected $attributes = [
        'recording_method' => 'periodik',
        'inventory_method' => 'Average',
        'status'           => 'aktif',
    ];

    // ===================== HELPERS =====================

    public static function getKodeUmkm(): string
    {
        $last = self::orderByDesc('created_at')->first();
        $lastNumber = $last ? (int) substr($last->kode_umkm, -5) : 0;
        return 'UM' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    // ===================== RELASI =====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(UmkmLevel::class, 'level_id');
    }

    public function bahanBaku()
    {
        return $this->hasMany(BahanBaku::class, 'umkm_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'umkm_id');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'umkm_id');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'umkm_id');
    }

    public function stokMutasi()
    {
        return $this->hasMany(StokMutasi::class, 'umkm_id');
    }

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'umkm_id');
    }

    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'umkm_id');
    }

    public function iuranBulanan()
    {
        return $this->hasMany(IuranBulanan::class, 'umkm_id');
    }
}
