<?php

namespace App\Services;

use App\Models\PeriodePenilaian;
use App\Models\Umkm;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Admin membuka dan menutup periode penilaian ulang (FR-21).
 * Saat dibuka, setiap UMKM berstatus aktif dinotifikasi; UMKM nonaktif tidak.
 */
class PeriodePenilaianService
{
    public function __construct(private NotificationService $notif) {}

    /**
     * @param  array{nama:string, tanggal_mulai:string, tanggal_selesai:string}  $data
     * @return array{periode: PeriodePenilaian, jumlah_dinotifikasi: int}
     *
     * @throws DomainException
     */
    public function buka(array $data, User $admin): array
    {
        $baris = [];
        $jumlah = 0;

        $periode = DB::transaction(function () use ($data, $admin, &$baris, &$jumlah) {
            // Hanya satu periode dibuka pada satu waktu; kunci agar dua Admin tidak membuka bersamaan.
            if (PeriodePenilaian::where('status', 'dibuka')->lockForUpdate()->exists()) {
                throw new DomainException('Masih ada periode yang dibuka. Tutup dulu periode tersebut.');
            }

            $periode = PeriodePenilaian::create([
                'nama'            => $data['nama'],
                'tanggal_mulai'   => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'status'          => 'dibuka',
                'dibuat_oleh'     => $admin->id,
            ]);

            foreach (Umkm::where('status', 'aktif')->with('user')->get() as $umkm) {
                $baris = array_merge($baris, $this->notif->catatPeriodeDibuka($umkm, $periode));
                $jumlah++;
            }

            $this->catat($admin, 'periode.buka', $periode, [], [
                'nama' => $periode->nama,
                'mulai' => $periode->tanggal_mulai->toDateString(),
                'selesai' => $periode->tanggal_selesai->toDateString(),
                'umkm_dinotifikasi' => $jumlah,
            ]);

            return $periode;
        });

        $this->notif->kirimTertunda($baris);

        return ['periode' => $periode, 'jumlah_dinotifikasi' => $jumlah];
    }

    /** @throws DomainException */
    public function tutup(int $periodeId, User $admin): PeriodePenilaian
    {
        return DB::transaction(function () use ($periodeId, $admin) {
            $periode = PeriodePenilaian::whereKey($periodeId)->lockForUpdate()->firstOrFail();

            if ($periode->status !== 'dibuka') {
                throw new DomainException('Periode ini sudah ditutup.');
            }

            $periode->update(['status' => 'ditutup']);

            $this->catat($admin, 'periode.tutup', $periode, ['status' => 'dibuka'], ['status' => 'ditutup']);

            return $periode;
        });
    }

    private function catat(User $admin, string $aksi, PeriodePenilaian $periode, array $lama, array $baru): void
    {
        DB::table('log_audit')->insert([
            'pengguna_id' => $admin->id,
            'aksi'        => $aksi,
            'entitas'     => 'periode_penilaian',
            'entitas_id'  => $periode->id,
            'data_lama'   => json_encode($lama),
            'data_baru'   => json_encode($baru),
            'waktu'       => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
