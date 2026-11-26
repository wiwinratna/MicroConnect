<?php

namespace App\Services;

use App\Models\JenisUsaha;
use App\Models\PenilaianLevel;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Admin menambah UMKM sendiri: kuesioner dinilai, Admin menetapkan level (alasan wajib
 * bila berbeda dari rekomendasi), akun memakai kata sandi awal yang sama (BR-01, BR-02, BR-08).
 */
class BuatUmkmAdminService
{
    public function __construct(
        private PenilaianService $penilaian,
        private NotificationService $notif,
    ) {
    }

    /**
     * @param array $data   data tervalidasi
     * @param array $flag   [memiliki_persediaan, melakukan_produksi]
     * @return array{0:User,1:Umkm,2:string} pengguna, UMKM, dan kata sandi awal (untuk email)
     *
     * @throws ValidationException
     */
    public function buat(array $data, array $flag, User $admin): array
    {
        $sandiAwal = config('minect.initial_password');

        if (blank($sandiAwal)) {
            $this->tolak('email', 'Kata sandi awal (INITIAL_PASSWORD) belum diatur. Hubungi pengelola sistem.');
        }

        $hasil      = $this->penilaian->hitung((array) $data['jawaban']);
        $levelAkhir = empty($data['level_id']) ? $hasil['level'] : UmkmLevel::findOrFail($data['level_id']);
        $berbeda    = $levelAkhir->id !== $hasil['level']->id;
        $alasan     = trim((string) ($data['alasan'] ?? ''));

        if ($berbeda && $alasan === '') {
            $this->tolak('alasan', 'Alasan wajib diisi jika level yang ditetapkan berbeda dari rekomendasi.');
        }

        $baris = [];

        [$user, $umkm] = DB::transaction(function () use ($data, $flag, $admin, $sandiAwal, $hasil, $levelAkhir, $berbeda, $alasan, &$baris) {
            $user = User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'password'             => Hash::make($sandiAwal),
                'user_group'           => 'pelakuusaha',
                'must_change_password' => (bool) config('minect.force_change_initial_password'),
            ]);

            $umkm = Umkm::create([
                'kode_umkm'                => Umkm::getKodeUmkm(),
                'user_id'                  => $user->id,
                'level_id'                 => $levelAkhir->id,
                'nama_usaha'               => $data['nama_usaha'],
                'nib'                      => $data['nib'] ?? null,
                'alamat'                   => $data['alamat'] ?? null,
                'no_telepon'               => $data['no_telepon'] ?? null,
                'no_whatsapp'              => $data['no_whatsapp'] ?? null,
                'jenis_usaha'              => JenisUsaha::findOrFail($data['jenis_usaha_id'])->namaPendek(),
                'jenis_usaha_id'           => $data['jenis_usaha_id'],
                'rumpun'                   => $data['rumpun'],
                'memiliki_persediaan'      => $flag[0],
                'melakukan_produksi'       => $flag[1],
                'persetujuan_data_pribadi' => true,
                'tanggal_persetujuan'      => now(),
                'tanggal_aktivasi'         => now()->toDateString(),
                'recording_method'         => $data['recording_method'] ?? 'periodik',
                'inventory_method'         => $data['inventory_method'] ?? 'Average',
                'status'                   => 'aktif',
            ]);

            $umkm->seedDefaultCoa();

            $penilaian = PenilaianLevel::create([
                'umkm_id'              => $umkm->id,
                'jenis'                => 'awal',
                'tanggal_penilaian'    => now()->toDateString(),
                'total_skor'           => $hasil['total'],
                'level_rekomendasi_id' => $hasil['level']->id,
                'arah_rekomendasi'     => 'baru',
                'level_akhir_id'       => $levelAkhir->id,
                'alasan_penetapan'     => $berbeda ? $alasan : null,
                'ditinjau_oleh'        => $admin->id,
                'tanggal_peninjauan'   => now(),
            ]);
            $penilaian->detail()->createMany($hasil['detail']);

            DB::table('log_audit')->insert([
                'pengguna_id' => $admin->id,
                'aksi'        => 'umkm.buat',
                'entitas'     => 'umkm',
                'entitas_id'  => $umkm->id,
                'data_lama'   => null,
                'data_baru'   => json_encode([
                    'level_rekomendasi' => $hasil['level']->kode,
                    'level_akhir'       => $levelAkhir->kode,
                    'alasan'            => $berbeda ? $alasan : null,
                ]),
                'waktu'       => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Login memakai email, jadi email disebut sebagai pengenal akun.
            $baris = $this->notif->catat('akun_aktif', [
                'nama_usaha'      => $umkm->nama_usaha,
                'username'        => $user->email,
                'kata_sandi_awal' => $sandiAwal,
            ], $this->notif->nomorTujuan($umkm), $umkm->id, null);

            return [$user, $umkm];
        });

        $this->notif->kirimTertunda($baris);

        return [$user, $umkm, $sandiAwal];
    }

    private function tolak(string $kolom, string $pesan): never
    {
        throw ValidationException::withMessages([$kolom => $pesan]);
    }
}
