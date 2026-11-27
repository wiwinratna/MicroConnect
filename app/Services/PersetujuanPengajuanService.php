<?php

namespace App\Services;

use App\Models\PengajuanPendaftaran;
use App\Models\PenilaianLevel;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Keputusan Admin atas pengajuan pendaftaran (FR-10, FR-11).
 * Akun (users + umkm) dibuat HANYA di sini, dalam satu transaksi (BR-13).
 * Kegagalan yang wajar dilempar sebagai DomainException berpesan Indonesia.
 */
class PersetujuanPengajuanService
{
    public function __construct(private NotificationService $notif) {}

    /**
     * Setujui pengajuan: buat akun, isi level akhir, catat keputusan.
     *
     * @throws DomainException
     */
    public function setujui(int $pengajuanId, int $levelAkhirId, ?string $alasan, User $admin): Umkm
    {
        $sandiAwal = config('minect.initial_password');

        if (blank($sandiAwal)) {
            throw new DomainException('Kata sandi awal (INITIAL_PASSWORD) belum diatur. Hubungi pengelola sistem.');
        }

        $baris = [];

        $umkm = DB::transaction(function () use ($pengajuanId, $levelAkhirId, $alasan, $admin, $sandiAwal, &$baris) {
            // Kunci baris agar klik ganda / dua Admin tidak membuat dua akun.
            $pengajuan = PengajuanPendaftaran::whereKey($pengajuanId)->lockForUpdate()->firstOrFail();
            $this->pastikanMenunggu($pengajuan);

            $penilaian = PenilaianLevel::where('pengajuan_id', $pengajuan->id)->lockForUpdate()->firstOrFail();
            $levelAkhir = UmkmLevel::findOrFail($levelAkhirId);

            $berbeda = $levelAkhir->id !== (int) $penilaian->level_rekomendasi_id;
            $alasan  = trim((string) $alasan);

            if ($berbeda && $alasan === '') {
                throw new DomainException('Alasan wajib diisi jika level akhir berbeda dari rekomendasi.');
            }

            $bentrok = User::where('email', $pengajuan->email)
                ->orWhere('no_wa', $pengajuan->no_wa)
                ->exists();

            if ($bentrok) {
                throw new DomainException(
                    'Email atau nomor WhatsApp pengajuan ini sudah dipakai akun lain, sehingga tidak dapat disetujui.'
                );
            }

            $user = User::create([
                'name'                 => $pengajuan->nama_pemilik,
                'email'                => $pengajuan->email,
                'no_wa'                => $pengajuan->no_wa,
                'password'             => Hash::make($sandiAwal),
                'user_group'           => 'pelakuusaha',
                // BR-08: wajib ganti saat login pertama, bila FORCE_CHANGE_INITIAL_PASSWORD=true.
                // Bawaan: tidak wajib; pemilik boleh mengganti sendiri lewat menu Profil.
                'must_change_password' => (bool) config('minect.force_change_initial_password'),
            ]);

            $umkm = Umkm::create([
                'kode_umkm'                => Umkm::getKodeUmkm(),
                'user_id'                  => $user->id,
                'level_id'                 => $levelAkhir->id,
                'nama_usaha'               => $pengajuan->nama_usaha,
                'nib'                      => $pengajuan->nib,
                'alamat'                   => $pengajuan->alamat,
                'no_telepon'               => $pengajuan->no_telepon,
                'no_whatsapp'              => $pengajuan->no_wa,
                'jenis_usaha'              => $this->namaPendekJenisUsaha($pengajuan),
                'jenis_usaha_id'           => $pengajuan->jenis_usaha_id,
                'rumpun'                   => $pengajuan->rumpun,
                'memiliki_persediaan'      => $pengajuan->memiliki_persediaan,
                'melakukan_produksi'       => $pengajuan->melakukan_produksi,
                'persetujuan_data_pribadi' => true,
                'tanggal_persetujuan'      => $pengajuan->tanggal_persetujuan,
                'tanggal_aktivasi'         => now()->toDateString(),
                'status'                   => 'aktif',
            ]);

            $umkm->seedDefaultCoa();

            $penilaian->update([
                'umkm_id'          => $umkm->id,
                'level_akhir_id'   => $levelAkhir->id,
                'alasan_penetapan' => $berbeda ? $alasan : null,
            ]);

            $pengajuan->update([
                'umkm_id'            => $umkm->id,
                'status'             => 'disetujui',
                'diverifikasi_oleh'  => $admin->id,
                'tanggal_verifikasi' => now(),
            ]);

            $this->catat($admin, 'pengajuan.setujui', $pengajuan, [
                'status' => 'menunggu',
                'level_rekomendasi' => UmkmLevel::whereKey($penilaian->level_rekomendasi_id)->value('kode'),
            ], [
                'status' => 'disetujui',
                'level_akhir' => $levelAkhir->kode,
                'alasan' => $berbeda ? $alasan : null,
                'umkm_id' => $umkm->id,
            ]);

            // FR-12 / BR-13: notifikasi akun aktif dicatat dalam transaksi yang sama.
            $baris = $this->notif->catatAkunAktif($pengajuan, $umkm, $sandiAwal);

            return $umkm;
        });

        // Pengiriman setelah transaksi selesai dan tidak memblokir proses utama.
        $this->notif->kirimTertunda($baris);

        return $umkm;
    }

    /**
     * Tolak pengajuan (alasan wajib). Tidak ada akun yang dibuat.
     *
     * @throws DomainException
     */
    public function tolak(int $pengajuanId, ?string $alasan, User $admin): PengajuanPendaftaran
    {
        $alasan = trim((string) $alasan);

        if ($alasan === '') {
            throw new DomainException('Alasan penolakan wajib diisi.');
        }

        $baris = [];

        $pengajuan = DB::transaction(function () use ($pengajuanId, $alasan, $admin, &$baris) {
            $pengajuan = PengajuanPendaftaran::whereKey($pengajuanId)->lockForUpdate()->firstOrFail();
            $this->pastikanMenunggu($pengajuan);

            $pengajuan->update([
                'status'             => 'ditolak',
                'alasan_penolakan'   => $alasan,
                'diverifikasi_oleh'  => $admin->id,
                'tanggal_verifikasi' => now(),
            ]);

            $this->catat($admin, 'pengajuan.tolak', $pengajuan,
                ['status' => 'menunggu'],
                ['status' => 'ditolak', 'alasan' => $alasan]
            );

            // FR-12 / BR-13: notifikasi penolakan dicatat dalam transaksi yang sama.
            $baris = $this->notif->catatPengajuanDitolak($pengajuan, $alasan);

            return $pengajuan;
        });

        $this->notif->kirimTertunda($baris);

        return $pengajuan;
    }

    private function pastikanMenunggu(PengajuanPendaftaran $pengajuan): void
    {
        if ($pengajuan->status !== 'menunggu') {
            throw new DomainException('Pengajuan ini sudah diputuskan sebelumnya.');
        }
    }

    /** Nama jenis usaha tanpa contoh dalam kurung, muat di kolom umkm.jenis_usaha (50 karakter). */
    private function namaPendekJenisUsaha(PengajuanPendaftaran $pengajuan): ?string
    {
        return $pengajuan->jenisUsaha?->namaPendek();
    }

    /** Log audit keputusan: siapa, apa, kapan, data lama dan baru (FR-26 sebagian). */
    private function catat(User $admin, string $aksi, PengajuanPendaftaran $pengajuan, array $lama, array $baru): void
    {
        DB::table('log_audit')->insert([
            'pengguna_id' => $admin->id,
            'aksi'        => $aksi,
            'entitas'     => 'pengajuan_pendaftaran',
            'entitas_id'  => $pengajuan->id,
            'data_lama'   => json_encode($lama),
            'data_baru'   => json_encode($baru),
            'waktu'       => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
