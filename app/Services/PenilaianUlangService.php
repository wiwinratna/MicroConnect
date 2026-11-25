<?php

namespace App\Services;

use App\Models\Indikator;
use App\Models\PeriodePenilaian;
use App\Models\PenilaianLevel;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Penilaian ulang (FR-22 s.d. FR-25).
 *  - bawaan()   : pilihan terisi otomatis (omzet/modal dari pembukuan, sisanya dari jawaban terakhir).
 *  - kirim()    : UMKM mengirim penilaian; sistem hanya merekomendasikan, level TIDAK berubah (BR-01).
 *  - tetapkan() : Admin menetapkan level akhir; baru di sini umkm.level_id berubah (FR-24, FR-25).
 */
class PenilaianUlangService
{
    public function __construct(
        private PenilaianService $penilaian,
        private DataSistemService $sistem,
        private NotificationService $notif,
    ) {}

    /** Periode yang sedang berlaku (status dibuka dan hari ini dalam rentang tanggal), atau null. */
    public function periodeBerlaku(): ?PeriodePenilaian
    {
        return PeriodePenilaian::where('status', 'dibuka')->latest('id')->get()
            ->first(fn (PeriodePenilaian $p) => $p->berlaku());
    }

    public function sudahMengisi(Umkm $umkm, PeriodePenilaian $periode): bool
    {
        return PenilaianLevel::where('umkm_id', $umkm->id)->where('periode_id', $periode->id)->exists();
    }

    /**
     * Isi bawaan formulir. Omzet dan modal dari pembukuan bila ada datanya; selain itu dari
     * jawaban penilaian terakhir. Semuanya boleh diubah UMKM.
     *
     * @return array{sistem: array, indikator: \Illuminate\Support\Collection, bawaan: array, sebelumnya: array}
     */
    public function bawaan(Umkm $umkm): array
    {
        $sistem    = $this->sistem->hitung($umkm);
        $indikator = Indikator::aktif()->with('pilihan')->orderBy('urutan')->get();

        $terakhir = PenilaianLevel::with('detail')
            ->where('umkm_id', $umkm->id)
            ->orderByDesc('tanggal_penilaian')->orderByDesc('id')->first();

        $bawaan = [];
        $sebelumnya = [];

        foreach ($indikator as $ind) {
            $pilihanId = null;
            $sumber = 'kosong';

            $jenis = $this->sistem->jenisSistem($ind);
            if ($jenis && $sistem[$jenis] !== null) {
                $cocok = $this->sistem->pilihanUntuk($ind, $sistem[$jenis]);
                if ($cocok) {
                    $pilihanId = $cocok->id;
                    $sumber = 'sistem';
                }
            }

            $detail = $terakhir?->detail->firstWhere('indikator_id', $ind->id);
            if ($detail) {
                $sebelumnya[$ind->id] = $detail->teks_pilihan;

                if ($pilihanId === null && $ind->pilihan->contains('id', $detail->pilihan_id)) {
                    $pilihanId = $detail->pilihan_id;
                    $sumber = 'sebelumnya';
                }
            }

            $bawaan[$ind->id] = ['pilihan_id' => $pilihanId, 'sumber' => $sumber];
        }

        return compact('sistem', 'indikator', 'bawaan', 'sebelumnya');
    }

    /**
     * UMKM mengirim penilaian ulang. Satu kali per UMKM per periode (FR-22).
     *
     * @throws DomainException pesan berbahasa Indonesia
     * @throws \InvalidArgumentException jika jawaban belum lengkap
     */
    public function kirim(Umkm $umkm, int $periodeId, array $jawaban): PenilaianLevel
    {
        $hasil  = $this->penilaian->hitung($jawaban);
        $bawaan = $this->bawaan($umkm);
        $snapshot = $this->snapshotSistem($bawaan);

        try {
            return DB::transaction(function () use ($umkm, $periodeId, $hasil, $snapshot) {
                $periode = PeriodePenilaian::whereKey($periodeId)->lockForUpdate()->firstOrFail();

                if (!$periode->berlaku()) {
                    throw new DomainException('Periode penilaian sedang tidak dibuka.');
                }

                if ($this->sudahMengisi($umkm, $periode)) {
                    throw new DomainException('Anda sudah mengisi penilaian pada periode ini.');
                }

                $penilaian = PenilaianLevel::create([
                    'umkm_id'              => $umkm->id,
                    'periode_id'           => $periode->id,
                    'jenis'                => 'ulang',
                    'tanggal_penilaian'    => now()->toDateString(),
                    'total_skor'           => $hasil['total'],
                    'level_rekomendasi_id' => $hasil['level']->id,
                    'arah_rekomendasi'     => $this->arah($hasil['level'], $umkm->level),
                    'status_peninjauan'    => 'menunggu',
                    'data_sistem'          => $snapshot,
                ]);

                $penilaian->detail()->createMany($hasil['detail']);

                return $penilaian;
            });
        } catch (QueryException $e) {
            // Klik ganda atau pengiriman bersamaan: indeks unik umkm_id + periode_id menolak yang kedua.
            if ($e->getCode() === '23000') {
                throw new DomainException('Anda sudah mengisi penilaian pada periode ini.');
            }

            throw $e;
        }
    }

    /**
     * Admin menetapkan level akhir (FR-24) lalu memperbarui level UMKM dan menotifikasi (FR-25).
     * Riwayat penilaian dan transaksi lama tidak dihapus (BR-04).
     *
     * @throws DomainException
     */
    public function tetapkan(
        int $penilaianId, int $levelAkhirId, ?string $alasan,
        ?string $catatan, ?string $tindakLanjut, User $admin
    ): PenilaianLevel {
        $baris = [];

        $penilaian = DB::transaction(function () use ($penilaianId, $levelAkhirId, $alasan, $catatan, $tindakLanjut, $admin, &$baris) {
            $penilaian = PenilaianLevel::whereKey($penilaianId)->where('jenis', 'ulang')->lockForUpdate()->firstOrFail();

            if ($penilaian->status_peninjauan !== 'menunggu') {
                throw new DomainException('Penilaian ini sudah ditinjau sebelumnya.');
            }

            $levelAkhir = UmkmLevel::findOrFail($levelAkhirId);
            $berbeda    = $levelAkhir->id !== (int) $penilaian->level_rekomendasi_id;
            $alasan     = trim((string) $alasan);

            if ($berbeda && $alasan === '') {
                throw new DomainException('Alasan wajib diisi jika level akhir berbeda dari rekomendasi.');
            }

            $umkm      = Umkm::whereKey($penilaian->umkm_id)->lockForUpdate()->firstOrFail();
            $levelLama = $umkm->level?->kode;

            $penilaian->update([
                'level_akhir_id'          => $levelAkhir->id,
                'alasan_penetapan'        => $berbeda ? $alasan : null,
                'status_peninjauan'       => 'selesai',
                'catatan_peninjauan'      => filled($catatan) ? trim($catatan) : null,
                'tindak_lanjut_pembinaan' => filled($tindakLanjut) ? trim($tindakLanjut) : null,
                'ditinjau_oleh'           => $admin->id,
                'tanggal_peninjauan'      => now(),
            ]);

            // Level baru berlaku hanya di sini, setelah Admin menyimpan (BR-01).
            $umkm->update(['level_id' => $levelAkhir->id]);

            DB::table('log_audit')->insert([
                'pengguna_id' => $admin->id,
                'aksi'        => 'penilaian_ulang.tetapkan',
                'entitas'     => 'penilaian_level',
                'entitas_id'  => $penilaian->id,
                'data_lama'   => json_encode(['level' => $levelLama]),
                'data_baru'   => json_encode([
                    'level' => $levelAkhir->kode,
                    'rekomendasi' => UmkmLevel::whereKey($penilaian->level_rekomendasi_id)->value('kode'),
                    'alasan' => $berbeda ? $alasan : null,
                    'umkm_id' => $umkm->id,
                ]),
                'waktu'       => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $baris = $this->notif->catatHasilPenilaian($umkm->load('user'), $levelAkhir->nama_level, $berbeda ? $alasan : null);

            return $penilaian;
        });

        $this->notif->kirimTertunda($baris);

        return $penilaian;
    }

    /** naik / tetap / turun menurut urutan level; 'baru' bila UMKM belum punya level. */
    public function arah(UmkmLevel $rekomendasi, ?UmkmLevel $saatIni): string
    {
        if (!$saatIni) {
            return 'baru';
        }

        return match (true) {
            $rekomendasi->urutan > $saatIni->urutan => 'naik',
            $rekomendasi->urutan < $saatIni->urutan => 'turun',
            default                                 => 'tetap',
        };
    }

    /** Rekam angka sistem saat formulir dikirim, untuk dibandingkan Admin dengan pilihan UMKM. */
    private function snapshotSistem(array $bawaan): array
    {
        $data = ['hari_data' => $bawaan['sistem']['hari_data'], 'dihitung_pada' => now()->toDateTimeString()];

        foreach ($bawaan['indikator'] as $ind) {
            $jenis = $this->sistem->jenisSistem($ind);

            if ($jenis) {
                $data[$jenis] = [
                    'nilai'              => $bawaan['sistem'][$jenis],
                    'indikator_id'       => $ind->id,
                    'pilihan_disarankan' => $bawaan['bawaan'][$ind->id]['sumber'] === 'sistem'
                        ? $bawaan['bawaan'][$ind->id]['pilihan_id'] : null,
                ];
            }
        }

        return $data;
    }
}
