<?php

namespace Tests\Concerns;

use App\Models\Indikator;
use App\Models\JurnalUmum;
use App\Models\PenilaianLevel;
use App\Models\PeriodePenilaian;
use App\Models\Umkm;
use App\Models\User;
use App\Services\PenilaianService;

/** Pembantu tes K4b: periode, jurnal, penilaian lama, dan pengiriman penilaian ulang. */
trait SiapkanPenilaianUlang
{
    use SiapkanModul;

    protected function siapkanK4b(): void
    {
        $this->seedK1();
    }

    /** Periode yang sedang berlaku (mulai kemarin, selesai dua minggu lagi) kecuali ditimpa. */
    protected function buatPeriode(array $ubah = []): PeriodePenilaian
    {
        return PeriodePenilaian::create(array_merge([
            'nama'            => 'Penilaian Ulang Uji',
            'tanggal_mulai'   => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'status'          => 'dibuka',
        ], $ubah));
    }

    protected function catatJurnal(int $umkmId, string $tanggal, string $kodeAkun, float $debit, float $kredit): void
    {
        JurnalUmum::create([
            'umkm_id' => $umkmId, 'tanggal' => $tanggal, 'kode_akun' => $kodeAkun,
            'nama_akun' => 'Akun ' . $kodeAkun, 'keterangan' => 'uji', 'debit' => $debit, 'kredit' => $kredit,
        ]);
    }

    /** Penilaian awal yang sudah ada (jawaban terakhir yang tersimpan) untuk pengisian bawaan. */
    protected function buatPenilaianLama(User $user, string $huruf, string $kodeLevelAkhir = 'L1'): PenilaianLevel
    {
        $hasil = app(PenilaianService::class)->hitung($this->jawab($huruf));

        $penilaian = PenilaianLevel::create([
            'umkm_id' => $user->umkm->id, 'jenis' => 'awal',
            'tanggal_penilaian' => now()->subMonths(6)->toDateString(),
            'total_skor' => $hasil['total'], 'level_rekomendasi_id' => $hasil['level']->id,
            'arah_rekomendasi' => 'baru', 'level_akhir_id' => $this->levelKode($kodeLevelAkhir)->id,
        ]);
        $penilaian->detail()->createMany($hasil['detail']);

        return $penilaian;
    }

    protected function pilihanId(int $urutanIndikator, string $huruf): int
    {
        return Indikator::where('urutan', $urutanIndikator)->firstOrFail()
            ->pilihan()->where('urutan', ord($huruf) - 96)->firstOrFail()->id;
    }

    protected function kirimUlang(User $user, string $huruf)
    {
        return $this->sebagai($user)->post(route('umkm.penilaian-ulang.store'), ['jawaban' => $this->jawab($huruf)]);
    }

    protected function umkmAktif(array $data = [], string $kodeLevel = 'L2'): User
    {
        return $this->buatUmkm(array_merge(['no_whatsapp' => '081211110000'], $data), $kodeLevel);
    }

    protected function ulangDari(User $user): ?PenilaianLevel
    {
        return PenilaianLevel::where('umkm_id', Umkm::where('user_id', $user->id)->value('id'))
            ->where('jenis', 'ulang')->latest('id')->first();
    }
}
