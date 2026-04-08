<?php

namespace App\Services;

use App\Models\IuranBulanan;
use App\Models\IuranPeriode;
use App\Models\Umkm;
use Carbon\Carbon;

/**
 * IuranService
 *
 * Mengelola logika iuran bulanan UMKM:
 * - Generate tagihan berdasarkan IuranPeriode
 * - Lazy create per UMKM per periode
 * - Mark lunas
 */
class IuranService
{
    /**
     * Generate tagihan iuran untuk semua UMKM aktif berdasarkan IuranPeriode.
     * Dipanggil saat admin membuat/menerbitkan periode iuran baru.
     *
     * @param IuranPeriode $iuranPeriode  Master periode iuran
     * @return int  Jumlah record baru yang dibuat
     */
    public function generateFromPeriode(IuranPeriode $iuranPeriode): int
    {
        $umkmAktif = Umkm::where('status', 'aktif')->get();

        $count = 0;
        foreach ($umkmAktif as $umkm) {
            // firstOrCreate agar tidak duplikat
            $created = IuranBulanan::firstOrCreate(
                [
                    'umkm_id' => $umkm->id,
                    'periode' => $iuranPeriode->periode,
                ],
                [
                    'iuran_periode_id' => $iuranPeriode->id,
                    'nominal'          => $iuranPeriode->nominal_default,
                    'status'           => 'belum_bayar',
                    'jatuh_tempo'      => $iuranPeriode->jatuh_tempo,
                ]
            );

            // Jika record baru dibuat (bukan existing), hitung
            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate tagihan iuran untuk semua UMKM aktif di periode tertentu.
     * Kompatibel dengan pemanggilan lama (tanpa master periode).
     *
     * @param string $periode  Format "2026-03" (default: bulan ini)
     * @return int  Jumlah record baru yang dibuat
     */
    public function generateMonthly(string $periode = null): int
    {
        $periode = $periode ?? now()->format('Y-m');

        // Cek apakah ada master periode
        $iuranPeriode = IuranPeriode::where('periode', $periode)->first();

        if ($iuranPeriode) {
            return $this->generateFromPeriode($iuranPeriode);
        }

        // Fallback legacy: tanpa master periode
        $defaultNominal = $this->getNominalIuran();
        $jatuhTempo = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        $umkmAktif = Umkm::where('status', 'aktif')->get();

        $count = 0;
        foreach ($umkmAktif as $umkm) {
            $created = IuranBulanan::firstOrCreate(
                ['umkm_id' => $umkm->id, 'periode' => $periode],
                [
                    'nominal'     => $defaultNominal,
                    'status'      => 'belum_bayar',
                    'jatuh_tempo' => $jatuhTempo,
                ]
            );

            if ($created->wasRecentlyCreated) {
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
        $periode    = $periode ?? now()->format('Y-m');
        $jatuhTempo = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        // Cek apakah ada master periode untuk ambil nominal & jatuh tempo
        $iuranPeriode = IuranPeriode::where('periode', $periode)->first();

        $nominal    = $iuranPeriode ? $iuranPeriode->nominal_default : $this->getNominalIuran();
        $jatuhTempo = $iuranPeriode ? $iuranPeriode->jatuh_tempo->toDateString() : $jatuhTempo;
        $periodeId  = $iuranPeriode ? $iuranPeriode->id : null;

        return IuranBulanan::firstOrCreate(
            ['umkm_id' => $umkmId, 'periode' => $periode],
            [
                'iuran_periode_id' => $periodeId,
                'nominal'          => $nominal,
                'status'           => 'belum_bayar',
                'jatuh_tempo'      => $jatuhTempo,
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
     * Ambil nominal iuran default.
     * Fallback ke env / hardcode jika tidak ada master periode.
     */
    private function getNominalIuran(): float
    {
        return (float) env('IURAN_DEFAULT', 50000);
    }
}
