<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanPenilaianUlang;
use Tests\TestCase;

/**
 * Pengisian otomatis formulir penilaian ulang: omzet dan modal dari pembukuan, sedangkan
 * tenaga kerja dan kebiasaan pencatatan dari jawaban terakhir. Semuanya tetap bisa diubah.
 */
class BawaanPenilaianUlangTest extends TestCase
{
    use RefreshDatabase, SiapkanPenilaianUlang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK4b();
        $this->buatPeriode();
    }

    /** Pilihan yang sudah terpilih di formulir (atribut checked pada radio). */
    private function assertTerpilih($respon, int $pilihanId): void
    {
        $this->assertMatchesRegularExpression(
            '/id="p' . $pilihanId . '"\s+value="' . $pilihanId . '"\s+checked/',
            $respon->getContent(),
            "pilihan {$pilihanId} seharusnya terpilih"
        );
    }

    private function assertTidakTerpilih($respon, int $pilihanId): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/id="p' . $pilihanId . '"\s+value="' . $pilihanId . '"\s+checked/',
            $respon->getContent()
        );
    }

    public function test_turnover_is_prefilled_from_twelve_month_bookkeeping(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->catatJurnal($u->umkm->id, now()->subDays(20)->toDateString(), '400', 0, 600_000_000);

        $respon = $this->ke($u, '/umkm/penilaian-ulang')->assertOk();

        $this->assertTerpilih($respon, $this->pilihanId(1, 'b'));   // 600 juta -> pilihan b
        $respon->assertSee('Menurut pembukuan Anda, omzet 12 bulan terakhir')
            ->assertSee('data tersedia 21 hari')
            ->assertSee('Ubah pilihan jika tidak sesuai');
    }

    public function test_capital_is_prefilled_from_bookkeeping(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $t = now()->subDays(30)->toDateString();
        $this->catatJurnal($u->umkm->id, $t, '311', 0, 150_000_000); // modal 150 juta -> pilihan b

        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $this->assertTerpilih($respon, $this->pilihanId(2, 'b'));
        $respon->assertSee('modal usaha saat ini');
    }

    public function test_labour_and_recording_habit_are_prefilled_from_last_answers(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->buatPenilaianLama($u, 'aabc'); // P3 b (2-3 orang), P4 c (setiap transaksi)

        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $this->assertTerpilih($respon, $this->pilihanId(3, 'b'));
        $this->assertTerpilih($respon, $this->pilihanId(4, 'c'));
        $respon->assertSee('Jawaban sebelumnya: 2–3 orang (termasuk pemilik)')
            ->assertSee('Terisi dari jawaban Anda sebelumnya');
    }

    public function test_bookkeeping_overrides_last_answer_for_turnover(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->buatPenilaianLama($u, 'aaaa'); // dulu omzet pilihan a
        $this->catatJurnal($u->umkm->id, now()->subDays(3)->toDateString(), '400', 0, 800_000_000); // kini 800 juta -> b

        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $this->assertTerpilih($respon, $this->pilihanId(1, 'b'));
        $this->assertTidakTerpilih($respon, $this->pilihanId(1, 'a'));
        $respon->assertSee('Jawaban sebelumnya: Sampai dengan Rp500 juta');
    }

    public function test_last_answer_fills_turnover_when_there_is_no_bookkeeping(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->buatPenilaianLama($u, 'cbbb');

        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $this->assertTerpilih($respon, $this->pilihanId(1, 'c'));
        $this->assertTerpilih($respon, $this->pilihanId(2, 'b'));
    }

    public function test_umkm_without_history_or_bookkeeping_gets_no_prefill(): void
    {
        $u = $this->umkmAktif([], 'L2');

        $respon = $this->ke($u, '/umkm/penilaian-ulang')->assertOk();

        $this->assertDoesNotMatchRegularExpression('/value="\d+"\s+checked/', $respon->getContent());
        $respon->assertDontSee('Menurut pembukuan Anda');
    }

    public function test_short_bookkeeping_is_shown_as_is_with_days_of_data(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->catatJurnal($u->umkm->id, now()->subDays(1)->toDateString(), '400', 0, 6_500_000);

        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $respon->assertSee('data tersedia 2 hari');
        $this->assertTerpilih($respon, $this->pilihanId(1, 'a')); // angka apa adanya, tidak diproyeksikan
    }

    public function test_prefilled_choices_can_be_changed_when_submitting(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->buatPenilaianLama($u, 'aabc');
        $this->catatJurnal($u->umkm->id, now()->subDays(5)->toDateString(), '400', 0, 700_000_000); // saran omzet b

        // UMKM mengubah omzet ke c dan tenaga kerja ke d, sisanya dibiarkan.
        $jawaban = $this->jawab('cabc');
        $jawaban[array_keys($jawaban)[2]] = $this->pilihanId(3, 'd');

        $this->sebagai($u)->post(route('umkm.penilaian-ulang.store'), ['jawaban' => $jawaban])->assertSessionHas('success');

        $n = $this->ulangDari($u);
        $this->assertSame(3 + 1 + 3 + 3, $n->total_skor); // skor dari pilihan yang dipilih: c, a, d, c
        $this->assertSame(
            'Lebih dari Rp1,2 miliar sampai dengan Rp2 miliar',
            $n->detail->firstWhere('indikator_id', array_keys($jawaban)[0])->teks_pilihan
        );
    }

    public function test_previous_answers_of_other_umkm_are_never_used(): void
    {
        $lain = $this->umkmAktif([], 'L2');
        $this->buatPenilaianLama($lain, 'cccc');
        $this->catatJurnal($lain->umkm->id, now()->subDays(5)->toDateString(), '400', 0, 3_000_000_000);

        $u = $this->umkmAktif([], 'L2');
        $respon = $this->ke($u, '/umkm/penilaian-ulang');

        $this->assertDoesNotMatchRegularExpression('/value="\d+"\s+checked/', $respon->getContent());
    }
}
