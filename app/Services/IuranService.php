<?php

namespace App\Services;

use App\Models\IuranBulanan;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * IuranService
 *
 * Mengelola logika iuran bulanan UMKM:
 * - Generate tagihan untuk semua UMKM aktif
 * - Lazy create: getOrCreate per UMKM per periode
 */
class IuranService
{
    /**
     * Generate tagihan iuran untuk semua UMKM aktif di periode tertentu.
     * Panggil via Artisan command tiap awal bulan.
     *
     * @param string $periode  Format "2026-03" (default: bulan ini)
     * @return int  Jumlah record baru yang dibuat
     */
    public function generateMonthly(string $periode = null): int
    {
        $periode = $periode ?? now()->format('Y-m');

        // Ambil nominal iuran dari level (semua level sama, ambil saja dari level manapun)
        // Fallback: ambil dari setting atau hardcode default
        $defaultNominal = $this->getNominalIuran();

        // Jatuh tempo: akhir bulan periode
        $jatuhTempo = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        $umkmAktif = Umkm::where('status', 'aktif')
                         ->whereNotNull('level_id')  // hanya yang sudah pilih level
                         ->get();

        $count = 0;
        foreach ($umkmAktif as $umkm) {
            // Hindari duplikasi dengan firstOrCreate
            $exists = IuranBulanan::where('umkm_id', $umkm->id)
                                  ->where('periode', $periode)
                                  ->exists();
            if (!$exists) {
                IuranBulanan::create([
                    'umkm_id'     => $umkm->id,
                    'periode'     => $periode,
                    'nominal'     => $defaultNominal,
                    'status'      => 'belum_bayar',
                    'jatuh_tempo' => $jatuhTempo,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Ambil atau buat tagihan iuran untuk UMKM tertentu di periode tertentu.
     * Berguna saat UMKM membuka halaman iuran dan belum ada record-nya.
     */
    public function getOrCreate(int $umkmId, string $periode = null): IuranBulanan
    {
        $periode      = $periode ?? now()->format('Y-m');
        $jatuhTempo   = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();
        $defaultNominal = $this->getNominalIuran();

        return IuranBulanan::firstOrCreate(
            ['umkm_id' => $umkmId, 'periode' => $periode],
            [
                'nominal'     => $defaultNominal,
                'status'      => 'belum_bayar',
                'jatuh_tempo' => $jatuhTempo,
            ]
        );
    }

    /**
     * Tandai iuran sebagai lunas (dipanggil dari Midtrans callback atau manual).
     */
    public function markLunas(IuranBulanan $iuran): void
    {
        $iuran->markLunas();
    }

    /**
     * Ambil nominal iuran.
     * Sesuai aturan bisnis: semua level iurannya sama.
     * Ambil dari UmkmLevel level pertama, fallback ke .env atau hardcode.
     */
    private function getNominalIuran(): float
    {
        $level = UmkmLevel::first();
        if ($level && $level->iuran_bulanan > 0) {
            return (float) $level->iuran_bulanan;
        }

        // Fallback ke env / default
        return (float) env('IURAN_DEFAULT', 50000);
    }
}
