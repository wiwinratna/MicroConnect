<?php

namespace Database\Seeders\Demo;

use App\Http\Controllers\PendaftaranController;
use App\Http\Requests\PendaftaranRequest;
use App\Models\Indikator;
use App\Models\JenisUsaha;
use App\Models\PengajuanPendaftaran;
use App\Models\User;
use App\Services\PersetujuanPengajuanService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * K5: data demo (3 UMKM aktif dengan transaksi dan 1 pengajuan `menunggu`).
 * Tidak masuk DatabaseSeeder. Aman dijalankan ulang: usaha yang sudah ada dilewati.
 * Butuh data awal (DatabaseSeeder) dan INITIAL_PASSWORD di .env.
 *
 * Jalankan: php artisan db:seed --class="Database\Seeders\Demo\DemoSeeder"
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Data demo tidak boleh diisi di lingkungan produksi.');
        }

        $admin = User::where('user_group', 'admin')->first()
            ?? throw new RuntimeException('Belum ada akun Admin. Jalankan DatabaseSeeder dulu.');

        // Notifikasi demo tidak boleh terkirim ke luar.
        config(['mail.default' => 'array', 'minect.notif_driver' => 'simulasi']);

        $hariIni = Carbon::now()->startOfDay();
        $bulan = [2, 1, 0];
        $bulan = array_map(fn ($n) => $hariIni->copy()->startOfMonth()->subMonthsNoOverflow($n), $bulan);
        $transaksi = new PembuatTransaksi();

        // db:seed mematikan proteksi mass assignment; nyalakan lagi agar controller berperilaku
        // sama seperti saat dipakai lewat aplikasi.
        $tanpaProteksi = Model::isUnguarded();
        Model::reguard();

        try {
            foreach (DataDemo::umkmAktif() as $data) {
                if ($this->sudahAda($data['daftar'])) {
                    continue;
                }

                DB::transaction(function () use ($data, $admin, $bulan, $hariIni, $transaksi) {
                    Carbon::setTestNow($bulan[0]->copy()->setTime(7, 0));
                    $pengajuan = $this->daftarkan($data['daftar']);
                    $umkm = app(PersetujuanPengajuanService::class)
                        ->setujui($pengajuan->id, $pengajuan->penilaian->level_rekomendasi_id, null, $admin);

                    $transaksi->isi($umkm, $data, $bulan, $hariIni);
                });
            }

            Carbon::setTestNow();

            $menunggu = DataDemo::pengajuanMenunggu();
            if (!$this->sudahAda($menunggu)) {
                $this->daftarkan($menunggu);
            }
        } finally {
            Carbon::setTestNow();
            auth()->forgetUser();
            Model::unguard($tanpaProteksi);
        }
    }

    protected function sudahAda(array $daftar): bool
    {
        $email = $daftar['form']['email'];

        return User::where('email', $email)->exists()
            || PengajuanPendaftaran::where('email', $email)->exists();
    }

    /** Daftarkan lewat formulir yang sama dengan calon UMKM (validasi dan skor ikut jalan). */
    protected function daftarkan(array $daftar): PengajuanPendaftaran
    {
        auth()->forgetUser();

        $jawaban = [];
        foreach (Indikator::aktif()->orderBy('urutan')->orderBy('id')->get() as $i => $indikator) {
            // Pertanyaan tambahan dari Admin dijawab dengan pilihan pertama.
            $posisi = ord($daftar['jawaban'][$i] ?? 'a') - 97;
            $pilihan = $indikator->pilihan()->get();
            $jawaban[$indikator->id] = ($pilihan->get($posisi) ?? $pilihan->firstOrFail())->id;
        }

        $kategori = JenisUsaha::where('nama', 'like', $daftar['kategori'] . '%')->firstOrFail();

        $request = PendaftaranRequest::create('/register', 'POST', $daftar['form'] + [
            'jenis_usaha_id' => $kategori->id,
            'jawaban'        => $jawaban,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        app()->instance('request', $request);
        $request->validateResolved();

        app()->call([app(PendaftaranController::class), 'store'], ['request' => $request]);

        return PengajuanPendaftaran::where('email', $daftar['form']['email'])->firstOrFail();
    }
}
