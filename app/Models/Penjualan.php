<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'umkm_id','tanggal','kode_penjualan','pembeli','catatan','total'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class, 'penjualan_id');
    }

    /**
     * Cek apakah penjualan dikunci (tidak bisa diedit/hapus).
     * Dikunci jika piutang sudah mulai dibayar.
     */
    public function isLocked(): bool
    {
        $piutang = $this->piutang;
        if ($piutang && $piutang->sudah_dibayar > 0) {
            return true;
        }
        return false;
    }
}
