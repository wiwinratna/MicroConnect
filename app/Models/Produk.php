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
        'satuan',
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

    /**
     * Biaya tiap bahan per 1 unit produk: qty resep (dalam satuan dasar bahan) x harga beli
     * terakhir. Dipakai tombol estimasi dan Daftar Produk agar angkanya sama.
     */
    public function rincianBiayaBahan(): array
    {
        $rincian = [];

        foreach ($this->komposisi()->with('bahan')->get() as $baris) {
            $bahan = $baris->bahan;
            // Baris resep lama yang menunjuk bahan UMKM lain diabaikan.
            if (!$bahan || (int) $bahan->umkm_id !== (int) $this->umkm_id) {
                continue;
            }

            $satuanResep = $baris->satuan ?? $bahan->satuan;
            $qtyResep    = (float) $baris->qty;
            $qtyDasar    = \App\Helpers\UnitConverter::isCompatible($satuanResep, $bahan->satuan)
                ? \App\Helpers\UnitConverter::convert($qtyResep, $satuanResep, $bahan->satuan)
                : $qtyResep;
            $harga = (float) $bahan->hargaBeliTerakhir();

            $rincian[] = [
                'nama_bahan'   => $bahan->nama_bahan,
                'qty_resep'    => $qtyResep,
                'satuan_resep' => $satuanResep,
                'qty_base'     => round($qtyDasar, 3),
                'satuan_dasar' => $bahan->satuan,
                'harga_unit'   => $harga,
                'biaya'        => $qtyDasar * $harga,
            ];
        }

        return $rincian;
    }

    /** Estimasi HPP per unit; null bila belum ada resep atau ada bahan yang belum pernah dibeli. */
    public function estimasiHpp(): ?float
    {
        $rincian = $this->rincianBiayaBahan();

        if ($rincian === [] || collect($rincian)->contains(fn ($r) => $r['harga_unit'] <= 0)) {
            return null;
        }

        return round(array_sum(array_column($rincian, 'biaya')), 2);
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
