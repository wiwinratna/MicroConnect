<?php

namespace App\Services\Notifikasi;

use InvalidArgumentException;

/**
 * Templat pesan notifikasi (master prompt Bagian 8), bahasa Indonesia.
 * Penampung {nama} diisi dari data; penampung yang tidak ada dibiarkan kosong.
 */
class TemplatPesan
{
    public const TEMPLAT = [
        'registrasi_diterima' => 'Halo {nama_pemilik}, registrasi {nama_usaha} di MINECT telah kami terima. Mohon menunggu verifikasi dari Admin KADIN.',
        'akun_aktif'          => 'Akun MINECT {nama_usaha} telah aktif. Username: {username}. Kata sandi awal: {kata_sandi_awal}. Silakan ganti kata sandi saat login pertama.',
        'pengajuan_ditolak'   => 'Pengajuan {nama_usaha} belum dapat disetujui. Alasan: {alasan}. Anda dapat mendaftar ulang.',
        'periode_dibuka'      => 'Periode penilaian ulang MINECT dibuka {tanggal_mulai}–{tanggal_selesai}. Silakan isi penilaian melalui menu Penilaian.',
        'hasil_penilaian'     => 'Hasil penilaian {nama_usaha}: level {level_akhir}. {alasan_jika_berbeda}',
    ];

    /** Salinan aplikasi akun_aktif: tanpa kata sandi (sudah dikirim ke WhatsApp). */
    public const AKUN_AKTIF_APLIKASI = 'Akun MINECT {nama_usaha} telah aktif. Username: {username}. Anda dapat mengganti kata sandi awal kapan saja lewat menu Profil UMKM.';

    public static function render(string $jenis, array $data, bool $untukAplikasi = false): string
    {
        $templat = ($untukAplikasi && $jenis === 'akun_aktif')
            ? self::AKUN_AKTIF_APLIKASI
            : (self::TEMPLAT[$jenis] ?? throw new InvalidArgumentException("Jenis notifikasi tidak dikenal: {$jenis}"));

        // Jika penampung langsung diikuti titik di templat, titik penutup pada nilainya dibuang
        // supaya tidak jadi dua titik ("alasan.." -> "alasan.").
        $pesan = preg_replace_callback('/\{(\w+)\}(\.)?/', function ($m) use ($data) {
            $nilai = (string) ($data[$m[1]] ?? '');

            return (isset($m[2]) && $m[2] === '.' ? rtrim($nilai, '.') : $nilai) . ($m[2] ?? '');
        }, $templat);

        return trim(preg_replace('/\s{2,}/', ' ', $pesan));
    }
}
