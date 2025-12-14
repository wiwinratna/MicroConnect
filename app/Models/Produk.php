<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    // karena nama tabelmu "produk" (bukan "produks")
    protected $table = 'produk';

    protected $fillable = [
        'umkm_id',
        'kode_produk',
        'nama_produk',
        'kategori',
        'harga_jual',
        'aktif',
        'keterangan',
        'stok',
        'harga_pokok',
        'foto_path',
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }

    public static function generateKode(): string
    {
        $last = self::orderByDesc('id')->first();
        $num  = $last ? (int) substr($last->kode_produk, -5) : 0;

        return 'PRD-' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
    }

    public function komposisi()
    {
        return $this->hasMany(ProdukBahan::class, 'produk_id');
    }

    public function recalculateHpp(): float
    {
        $totalHpp = 0;

        // loop semua bahan di komposisi
        foreach ($this->komposisi()->with('bahan')->get() as $row) {
            $bahan = $row->bahan;
            if (!$bahan) {
                continue;
            }

            // harga rata-rata per 1 satuan bahan (misal per 1 gram / 1 ml / 1 pcs)
            $avgCost = $bahan->getAverageCost(); 

            // qty per 1 unit produk (sudah dalam satuan dasar bahan, karena tadi kita konversi)
            $qtyPerUnit = (float) $row->qty;

            // biaya bahan ini per 1 unit produk
            $biayaBahan = $qtyPerUnit * $avgCost;

            $totalHpp += $biayaBahan;
        }

        // TODO: nanti bisa ditambah overhead tenaga kerja, listrik, gas, dll di sini

        // simpan ke database
        $this->harga_pokok = $totalHpp;
        $this->save();

        return $totalHpp;
    }

}
