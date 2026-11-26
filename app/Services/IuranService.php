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
 * - Nominal mengikuti level UMKM (BR-15); level beriuran Rp0 tidak ditagih
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
            $tagihan = $this->buatTagihan(
                $umkm, $iuranPeriode->periode, $iuranPeriode->jatuh_tempo->toDateString(), $iuranPeriode
            );

            // Hitung hanya tagihan yang baru dibuat
            if ($tagihan?->wasRecentlyCreated) {
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
        $jatuhTempo = Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        $umkmAktif = Umkm::where('status', 'aktif')->get();

        $count = 0;
        foreach ($umkmAktif as $umkm) {
            if ($this->buatTagihan($umkm, $periode, $jatuhTempo)?->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Ambil atau buat tagihan iuran untuk UMKM tertentu di periode tertentu.
     * Berguna saat UMKM membuka halaman iuran dan belum ada record-nya.
     * Null bila tidak ada yang perlu ditagih.
     */
    public function getOrCreate(int $umkmId, string $periode = null): ?IuranBulanan
    {
        $periode      = $periode ?? now()->format('Y-m');
        $iuranPeriode = IuranPeriode::where('periode', $periode)->first();
        $jatuhTempo   = $iuranPeriode
            ? $iuranPeriode->jatuh_tempo->toDateString()
            : Carbon::createFromFormat('Y-m', $periode)->endOfMonth()->toDateString();

        return $this->buatTagihan(Umkm::findOrFail($umkmId), $periode, $jatuhTempo, $iuranPeriode);
    }

    /** Tagihan yang sudah ada dikembalikan apa adanya; yang baru mengikuti level UMKM. */
    private function buatTagihan(Umkm $umkm, string $periode, string $jatuhTempo, ?IuranPeriode $master = null): ?IuranBulanan
    {
        $kunci = ['umkm_id' => $umkm->id, 'periode' => $periode];

        if ($ada = IuranBulanan::where($kunci)->first()) {
            return $ada;
        }

        $nominal = $this->nominalUntuk($umkm, $master);

        if ($nominal <= 0) {
            return null;
        }

        return IuranBulanan::firstOrCreate($kunci, [
            'iuran_periode_id' => $master?->id,
            'nominal'          => $nominal,
            'status'           => 'belum_bayar',
            'jatuh_tempo'      => $jatuhTempo,
        ]);
    }

    /** UMKM lama tanpa level memakai nominal periode, atau nominal bawaan. */
    private function nominalUntuk(Umkm $umkm, ?IuranPeriode $master): float
    {
        if ($umkm->level) {
            return (float) $umkm->level->iuran_bulanan;
        }

        return (float) ($master?->nominal_default ?? $this->getNominalIuran());
    }

    /**
     * Tandai iuran sebagai lunas (dipanggil dari Midtrans callback atau manual).
     */
    public function markLunas(IuranBulanan $iuran): void
    {
        $iuran->markLunas();
    }

    /**
     * Nominal bawaan untuk UMKM tanpa level bila tidak ada master periode.
     */
    private function getNominalIuran(): float
    {
        return (float) env('IURAN_DEFAULT', 50000);
    }
}
