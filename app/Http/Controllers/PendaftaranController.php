<?php

namespace App\Http\Controllers;

use App\Http\Requests\PendaftaranRequest;
use App\Models\Indikator;
use App\Models\JenisUsaha;
use App\Models\PengajuanPendaftaran;
use App\Models\PenilaianLevel;
use App\Services\NotificationService;
use App\Services\PenilaianService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Registrasi mandiri calon UMKM (FR-01 s.d. FR-05).
 * Tidak ada login dan tidak ada akun: data hanya masuk ke pengajuan_pendaftaran
 * setelah formulir dikirim lengkap (BR-06). Calon tidak melihat skor/level (BR-03).
 */
class PendaftaranController extends Controller
{
    public function create()
    {
        return view('auth.register', [
            'jenisUsaha' => JenisUsaha::aktif()->orderBy('id')->get(),
            'indikator'  => Indikator::aktif()->with('pilihan')->orderBy('urutan')->get(),
        ]);
    }

    public function store(PendaftaranRequest $request, PenilaianService $penilaian, NotificationService $notifikasi)
    {
        try {
            $hasil = $penilaian->hitung((array) $request->input('jawaban', []));
        } catch (InvalidArgumentException | RuntimeException $e) {
            return back()->withErrors(['jawaban' => $e->getMessage()])->withInput();
        }

        [$memilikiPersediaan, $melakukanProduksi] = $request->flagUsaha();

        try {
            // Satu transaksi: pengajuan + penilaian + detail jawaban, semua atau tidak sama sekali.
            $pengajuan = DB::transaction(function () use ($request, $hasil, $memilikiPersediaan, $melakukanProduksi) {
                $pengajuan = PengajuanPendaftaran::create([
                    'nama_pemilik'             => $request->input('nama_pemilik'),
                    'email'                    => $request->input('email'),
                    'username'                 => $request->input('username'),
                    'no_wa'                    => $request->input('no_wa'),
                    'nama_usaha'               => $request->input('nama_usaha'),
                    'nib'                      => $request->input('nib'),
                    'alamat'                   => $request->input('alamat'),
                    'no_telepon'               => $request->input('no_telepon'),
                    'jenis_usaha_id'           => $request->input('jenis_usaha_id'),
                    'rumpun'                   => $request->input('rumpun'),
                    'memiliki_persediaan'      => $memilikiPersediaan,
                    'melakukan_produksi'       => $melakukanProduksi,
                    'persetujuan_data_pribadi' => true,
                    'tanggal_persetujuan'      => now(),
                    'perlu_keputusan_admin'    => $hasil['perlu_keputusan_admin'],
                    'tanggal_pengajuan'        => now(),
                    'status'                   => 'menunggu',
                ]);

                $penilaianLevel = PenilaianLevel::create([
                    'pengajuan_id'         => $pengajuan->id,
                    'jenis'                => 'awal',
                    'tanggal_penilaian'    => now()->toDateString(),
                    'total_skor'           => $hasil['total'],
                    'level_rekomendasi_id' => $hasil['level']->id,
                    'arah_rekomendasi'     => 'baru',
                ]);

                $penilaianLevel->detail()->createMany($hasil['detail']);

                return $pengajuan;
            });
        } catch (QueryException $e) {
            // Klik ganda / pengiriman bersamaan: indeks unik menolak pengajuan kedua.
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'email' => 'Pendaftaran dengan data ini sudah terkirim dan sedang menunggu verifikasi.',
                ])->withInput();
            }

            throw $e;
        }

        // FR-06: notifikasi registrasi diterima. Kegagalan di sini tidak boleh menggagalkan
        // pendaftaran yang sudah tersimpan, dan pengirimannya berjalan setelah respons.
        try {
            $notifikasi->kirimTertunda($notifikasi->catatRegistrasiDiterima($pengajuan));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('register.selesai')->with('pendaftaran_selesai', true)
            ->with('id_login', \App\Support\PengenalLogin::dariUsername($request->input('username')));
    }

    public function selesai()
    {
        if (!session('pendaftaran_selesai')) {
            return redirect()->route('register');
        }

        return view('auth.register-selesai');
    }
}
