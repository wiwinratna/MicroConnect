<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';

    protected $fillable = [
        'kode_bahan',
        'umkm_id',
        'nama_bahan',
        'satuan',
        'stok_awal',
        'keterangan',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected $attributes = [
        'is_archived' => false,
    ];

    public static function getKodeBahan()
    {
        $last = self::orderByDesc('created_at')->first();
        $lastNumber = $last ? (int) substr($last->kode_bahan, -5) : 0;
        $nextNumber = $lastNumber + 1;

        return 'BB' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        // misal: BB00001, BB00002, ...
    }

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function dipakaiDiProduk()
    {
        return $this->hasMany(ProdukBahan::class, 'bahan_id');
    }

    public function pembelianDetails()
    {
        return $this->hasMany(\App\Models\PembelianDetail::class, 'bahan_id');
    }

    /**
     * Hitung harga rata-rata bahan ini (berdasarkan pembelian detail)
     */
    public function getAverageCost(): float
    {
        // kalau mau per UMKM, filter juga pembeliannya by umkm_id
        $details = $this->pembelianDetails()
            ->whereHas('pembelian', function ($q) {
                $q->where('umkm_id', $this->umkm_id);
            })
            ->get();

        if ($details->isEmpty()) {
            return 0;
        }

        $totalQty = $details->sum('qty');
        $totalNilai = $details->sum(function ($d) {
            return $d->subtotal; // atau $d->qty * $d->harga_satuan
        });

        if ($totalQty <= 0) {
            return 0;
        }

        return $totalNilai / $totalQty; // harga per 1 satuan dasar
    }

    public function hargaBeliTerakhir(): float
    {
        return (float) \App\Models\PembelianDetail::query()
            ->where('bahan_id', $this->id)
            ->whereHas('pembelian', function ($q) {
                $q->where('umkm_id', $this->umkm_id);
            })
            ->latest('id') // atau latest('created_at')
            ->value('harga_beli') ?? 0;
    }
}
