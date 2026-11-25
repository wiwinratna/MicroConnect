<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * v1.1 (K0): pemetaan teks `umkm.jenis_usaha` lama ke `jenis_usaha_id` dan `rumpun`.
 *
 * - Hanya dua rumpun: barang dan jasa. Pemetaan disetujui pengguna.
 * - `memiliki_persediaan` dan `melakukan_produksi` sengaja dibiarkan null
 *   (belum ada datanya); Admin melengkapi lewat halaman edit UMKM (FR-27).
 * - Hanya baris dengan `rumpun` masih kosong yang disentuh; teks yang tidak
 *   dikenal dibiarkan. Aman dijalankan pada DB tanpa UMKM (no-op).
 */
return new class extends Migration
{
    /** teks lama (huruf kecil) => [awalan nama jenis_usaha atau null, rumpun] */
    private const PEMETAAN = [
        'kuliner'           => ['Jasa boga', 'barang'],
        'makanan & minuman' => ['Jasa boga', 'barang'],
        'perdagangan'       => ['Perdagangan barang', 'barang'],
        'fashion & tekstil' => ['Usaha kreatif', 'barang'],
        'manufaktur'        => [null, 'barang'],
        'jasa'              => ['Jasa perorangan', 'jasa'],
    ];

    public function up(): void
    {
        foreach (self::PEMETAAN as $teksLama => [$awalan, $rumpun]) {
            $jenisId = $awalan
                ? DB::table('jenis_usaha')->where('nama', 'like', $awalan . '%')->value('id')
                : null;

            DB::table('umkm')
                ->whereNull('rumpun')
                ->whereRaw('LOWER(TRIM(jenis_usaha)) = ?', [$teksLama])
                ->update(['jenis_usaha_id' => $jenisId, 'rumpun' => $rumpun]);
        }
    }

    public function down(): void
    {
        // Kembalikan hanya baris yang belum dilengkapi Admin (kedua flag masih null).
        DB::table('umkm')
            ->whereNull('memiliki_persediaan')
            ->whereNull('melakukan_produksi')
            ->whereIn(DB::raw('LOWER(TRIM(jenis_usaha))'), array_keys(self::PEMETAAN))
            ->update(['jenis_usaha_id' => null, 'rumpun' => null]);
    }
};
