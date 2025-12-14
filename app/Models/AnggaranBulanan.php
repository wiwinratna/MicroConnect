<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnggaranBulanan extends Model
{
    use HasFactory;

    protected $table = 'anggaran_bulanan';

    protected $fillable = [
        'umkm_id', 'periode', 'target_unit', 'total', 'catatan'
    ];

    // app/Models/AnggaranBulanan.php


    public function items()
    {
        return $this->hasMany(AnggaranBulananItem::class, 'anggaran_id');
    }

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    // kalau kolom total kamu selalu disimpan (recommended), pakai ini:
    public function totalOverhead(): float
    {
        // aman kalau null
        return (float) ($this->total ?? 0);
    }

    // overhead per 1 unit gabungan (untuk semua produk)
    public function overheadPerUnit(): float
    {
        $target = (float) ($this->target_unit ?? 0);
        if ($target <= 0) return 0.0;

        return $this->totalOverhead() / $target;
    }

    // OPTIONAL: kalau kamu mau total otomatis = sum(items)
    public function refreshTotalFromItems(): void
    {
        $sum = (float) $this->items()->sum('nominal'); // pastikan kolom item = nominal
        $this->total = $sum;
        $this->save();
    }
}
