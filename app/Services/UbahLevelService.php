<?php

namespace App\Services;

use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Perubahan level oleh Admin di luar penilaian: alasan wajib, tercatat di log audit,
 * dan UMKM diberi tahu (BR-01, FR-26). Data transaksi lama tidak disentuh (BR-04).
 */
class UbahLevelService
{
    public function __construct(private NotificationService $notif) {}

    /** @throws ValidationException */
    public function ubah(int $umkmId, int $levelId, string $alasan, User $admin): Umkm
    {
        $baris = [];

        $umkm = DB::transaction(function () use ($umkmId, $levelId, $alasan, $admin, &$baris) {
            $umkm = Umkm::whereKey($umkmId)->lockForUpdate()->firstOrFail();
            $baru = UmkmLevel::findOrFail($levelId);
            $lama = $umkm->level_id ? UmkmLevel::find($umkm->level_id) : null;

            if ($lama?->id === $baru->id) {
                throw ValidationException::withMessages(['level_id' => 'Level tidak berubah. Pilih level lain.']);
            }

            $umkm->update(['level_id' => $baru->id]);

            DB::table('log_audit')->insert([
                'pengguna_id' => $admin->id,
                'aksi'        => 'umkm.ubah_level',
                'entitas'     => 'umkm',
                'entitas_id'  => $umkm->id,
                'data_lama'   => json_encode(['level' => $lama?->kode]),
                'data_baru'   => json_encode(['level' => $baru->kode, 'alasan' => $alasan]),
                'waktu'       => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $baris = $this->notif->catatHasilPenilaian($umkm, $baru->nama_level, $alasan);

            return $umkm;
        });

        $this->notif->kirimTertunda($baris);

        return $umkm;
    }
}
