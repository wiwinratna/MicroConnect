<?php

namespace App\Services;

use App\Support\PengenalLogin;
use App\Models\Notifikasi;
use App\Models\PengajuanPendaftaran;
use App\Models\Umkm;
use App\Services\Notifikasi\NotifikasiDriver;
use App\Services\Notifikasi\TemplatPesan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Notifikasi satu arah (FR-06, FR-12). Dua tahap agar tidak memblokir proses utama:
 *  1) catat()          : menulis baris notifikasi; dipanggil DI DALAM transaksi keputusan (BR-13).
 *  2) kirimTertunda()  : mengirim lewat driver SETELAH respons dikirim ke pengguna.
 *
 * Tiap kejadian menulis dua baris: whatsapp (lewat driver; gagal tidak menghentikan apa pun)
 * dan aplikasi (salinan untuk Kotak Notifikasi, selalu tersedia). Tanpa pengulangan kirim.
 */
class NotificationService
{
    public function __construct(private NotifikasiDriver $driver) {}

    /**
     * Tulis baris notifikasi. Baris whatsapp dilewati jika tidak ada nomor tujuan.
     *
     * @return Notifikasi[] baris whatsapp yang perlu dikirim
     */
    public function catat(string $jenis, array $data, ?string $noTujuan, ?int $umkmId, ?int $pengajuanId): array
    {
        $noTujuan = trim((string) $noTujuan);
        $perluKirim = [];

        // Salinan aplikasi: langsung dianggap terkirim, tidak bergantung pada WhatsApp.
        Notifikasi::create([
            'umkm_id'      => $umkmId,
            'pengajuan_id' => $pengajuanId,
            'no_tujuan'    => $noTujuan !== '' ? $noTujuan : '-',
            'jenis'        => $jenis,
            'saluran'      => 'aplikasi',
            'isi'          => TemplatPesan::render($jenis, $data, untukAplikasi: true),
            'status'       => 'terkirim',
            'dikirim_pada' => now(),
        ]);

        if ($noTujuan !== '') {
            $perluKirim[] = Notifikasi::create([
                'umkm_id'      => $umkmId,
                'pengajuan_id' => $pengajuanId,
                'no_tujuan'    => $noTujuan,
                'jenis'        => $jenis,
                'saluran'      => 'whatsapp',
                'isi'          => TemplatPesan::render($jenis, $data),
                'status'       => 'antri',
            ]);
        }

        return $perluKirim;
    }

    public function catatRegistrasiDiterima(PengajuanPendaftaran $pengajuan): array
    {
        return $this->catat('registrasi_diterima', [
            'nama_pemilik' => $pengajuan->nama_pemilik,
            'nama_usaha'   => $pengajuan->nama_usaha,
        ], $pengajuan->no_wa, null, $pengajuan->id);
    }

    public function catatAkunAktif(PengajuanPendaftaran $pengajuan, Umkm $umkm, string $sandiAwal): array
    {
        return $this->catat('akun_aktif', [
            'nama_usaha'      => $pengajuan->nama_usaha,
            'username'        => PengenalLogin::dariUsername($pengajuan->username),
            'kata_sandi_awal' => $sandiAwal,
        ], $pengajuan->no_wa, $umkm->id, $pengajuan->id);
    }

    public function catatPengajuanDitolak(PengajuanPendaftaran $pengajuan, string $alasan): array
    {
        return $this->catat('pengajuan_ditolak', [
            'nama_usaha' => $pengajuan->nama_usaha,
            'alasan'     => $alasan,
        ], $pengajuan->no_wa, null, $pengajuan->id);
    }

    /** FR-21: pemberitahuan periode penilaian ulang dibuka, untuk satu UMKM aktif. */
    public function catatPeriodeDibuka(Umkm $umkm, \App\Models\PeriodePenilaian $periode): array
    {
        return $this->catat('periode_dibuka', [
            'tanggal_mulai'   => $periode->tanggal_mulai->format('d/m/Y'),
            'tanggal_selesai' => $periode->tanggal_selesai->format('d/m/Y'),
        ], $this->nomorTujuan($umkm), $umkm->id, null);
    }

    /** FR-25: hasil penilaian; alasan hanya disebut bila level akhir berbeda dari rekomendasi. */
    public function catatHasilPenilaian(Umkm $umkm, string $levelAkhir, ?string $alasanJikaBerbeda): array
    {
        return $this->catat('hasil_penilaian', [
            'nama_usaha'          => $umkm->nama_usaha,
            'level_akhir'         => $levelAkhir,
            'alasan_jika_berbeda' => filled($alasanJikaBerbeda) ? 'Alasan: ' . $alasanJikaBerbeda : '',
        ], $this->nomorTujuan($umkm), $umkm->id, null);
    }

    /** Nomor WhatsApp akun (users.no_wa) atau, untuk UMKM lama, umkm.no_whatsapp; kosong bila tidak ada. */
    public function nomorTujuan(Umkm $umkm): string
    {
        $nomor = $umkm->user?->no_wa ?: $umkm->no_whatsapp;

        return \App\Helpers\NomorWa::normalisasi($nomor);
    }

    /**
     * Kirim baris yang sudah tercatat. Di HTTP dijalankan setelah respons terkirim;
     * di konsol (artisan, tes) langsung dijalankan.
     *
     * @param Notifikasi[] $baris
     */
    public function kirimTertunda(array $baris): void
    {
        $ids = array_map(fn (Notifikasi $n) => $n->id, $baris);

        if ($ids === []) {
            return;
        }

        $proses = function () use ($ids) {
            foreach ($ids as $id) {
                $this->proses($id);
            }
        };

        app()->runningInConsole() ? $proses() : app()->terminating($proses);
    }

    /** Kirim satu notifikasi lewat driver; kegagalan dicatat, tidak dilempar ke pemanggil. */
    public function proses(int $id): void
    {
        $notifikasi = Notifikasi::find($id);

        if (!$notifikasi || $notifikasi->status !== 'antri') {
            return; // sudah diproses (idempoten)
        }

        try {
            $this->driver->kirim($notifikasi);

            $notifikasi->update([
                'status'       => 'terkirim',
                'percobaan'    => $notifikasi->percobaan + 1,
                'dikirim_pada' => now(),
                'pesan_galat'  => null,
            ]);
        } catch (Throwable $e) {
            Log::warning('[notifikasi] pengiriman gagal', ['id' => $id, 'jenis' => $notifikasi->jenis, 'galat' => $e->getMessage()]);

            $notifikasi->update([
                'status'      => 'gagal',
                'percobaan'   => $notifikasi->percobaan + 1,
                'pesan_galat' => Str::limit($e->getMessage(), 500, ''),
            ]);
        }
    }
}
