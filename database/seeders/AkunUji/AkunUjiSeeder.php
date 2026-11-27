<?php

namespace Database\Seeders\AkunUji;

use App\Models\UmkmLevel;
use App\Models\User;
use App\Services\PersetujuanPengajuanService;
use Carbon\Carbon;
use Database\Seeders\Demo\DemoSeeder;
use Database\Seeders\Demo\PembuatTransaksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Akun khusus uji coba: 1 Admin dan 6 UMKM (tiap level dan jenis usaha), dengan transaksi
 * contoh tiga bulan. Aman dijalankan ulang (akun yang sudah ada dilewati), ditolak di
 * produksi, kata sandi dari INITIAL_PASSWORD. Daftar akun: docs/AKUN_UJI.md.
 *
 * Jalankan: php artisan db:seed --class="Database\Seeders\AkunUji\AkunUjiSeeder"
 */
class AkunUjiSeeder extends DemoSeeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Akun uji tidak boleh dibuat di lingkungan produksi.');
        }

        $sandi = config('minect.initial_password');
        if (blank($sandi)) {
            throw new RuntimeException('INITIAL_PASSWORD belum diatur di .env.');
        }

        // Notifikasi akun uji tidak boleh terkirim ke luar.
        config(['mail.default' => 'array', 'minect.notif_driver' => 'simulasi']);

        $admin = User::firstOrCreate(['email' => 'adminuji@contoh.test'], [
            'name'                 => 'Admin Uji',
            'password'             => Hash::make($sandi),
            'user_group'           => 'admin',
            'must_change_password' => false,
        ]);

        $hariIni = Carbon::now()->startOfDay();
        $bulan = array_map(
            fn ($n) => $hariIni->copy()->startOfMonth()->subMonthsNoOverflow($n),
            [2, 1, 0]
        );
        // Jam 13 agar kode transaksi tidak bentrok dengan data demo (jam 8).
        $transaksi = new PembuatTransaksi(13);

        $tanpaProteksi = Model::isUnguarded();
        Model::reguard();

        try {
            foreach (DataAkunUji::umkm() as $data) {
                if ($this->sudahAda($data['daftar'])) {
                    continue;
                }

                DB::transaction(function () use ($data, $admin, $bulan, $hariIni, $transaksi) {
                    Carbon::setTestNow($bulan[0]->copy()->setTime(7, 0));
                    $pengajuan = $this->daftarkan($data['daftar']);

                    $level = UmkmLevel::where('kode', $data['level'])->firstOrFail();
                    $alasan = $level->id === $pengajuan->penilaian->level_rekomendasi_id
                        ? null : 'Akun uji coba: level ditentukan agar semua tampilan bisa dilihat.';
                    $umkm = app(PersetujuanPengajuanService::class)
                        ->setujui($pengajuan->id, $level->id, $alasan, $admin);

                    $transaksi->isi($umkm, $data, $bulan, $hariIni);
                });
            }
        } finally {
            Carbon::setTestNow();
            auth()->forgetUser();
            Model::unguard($tanpaProteksi);
        }
    }
}
