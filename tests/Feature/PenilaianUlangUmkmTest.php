<?php

namespace Tests\Feature;

use App\Models\PenilaianLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanPenilaianUlang;
use Tests\TestCase;

/**
 * FR-22 dan FR-23: UMKM mengisi penilaian ulang; sistem hanya merekomendasikan arah.
 */
class PenilaianUlangUmkmTest extends TestCase
{
    use RefreshDatabase, SiapkanPenilaianUlang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK4b();
    }

    // ---------- FR-22: kapan boleh mengisi ----------

    public function test_form_is_shown_while_the_period_is_in_effect(): void
    {
        $this->buatPeriode(['nama' => 'Penilaian Ulang Aktif']);
        $u = $this->umkmAktif();

        $this->ke($u, '/umkm/penilaian-ulang')->assertOk()
            ->assertSee('Penilaian Ulang Aktif')
            ->assertSee('Berapa hasil penjualan (omzet) usaha Anda dalam satu tahun terakhir?')
            ->assertSee('Kirim penilaian ulang');
    }

    public function test_form_is_closed_without_period_or_outside_it(): void
    {
        $u = $this->umkmAktif();
        $this->ke($u, '/umkm/penilaian-ulang')->assertOk()->assertSee('tidak ada periode penilaian ulang yang dibuka');

        $this->buatPeriode(['status' => 'ditutup']);
        $this->ke($u, '/umkm/penilaian-ulang')->assertSee('tidak ada periode penilaian ulang yang dibuka');

        $this->buatPeriode(['tanggal_mulai' => now()->addDays(2)->toDateString(), 'tanggal_selesai' => now()->addDays(9)->toDateString()]);
        $this->ke($u, '/umkm/penilaian-ulang')->assertSee('tidak ada periode penilaian ulang yang dibuka');
    }

    public function test_submission_is_refused_when_period_is_not_open(): void
    {
        $u = $this->umkmAktif();

        $this->kirimUlang($u, 'abab')->assertRedirect(route('umkm.penilaian-ulang.index'))->assertSessionHas('error');

        $this->buatPeriode(['status' => 'ditutup']);
        $this->kirimUlang($u, 'abab')->assertSessionHas('error');

        $this->assertSame(0, PenilaianLevel::where('jenis', 'ulang')->count());
    }

    public function test_one_submission_per_umkm_per_period(): void
    {
        $periode = $this->buatPeriode();
        $u = $this->umkmAktif();

        $this->kirimUlang($u, 'abab')->assertRedirect(route('umkm.penilaian.index'))->assertSessionHas('success');
        $this->kirimUlang($u, 'cccc')->assertSessionHas('error');

        $this->assertSame(1, PenilaianLevel::where('periode_id', $periode->id)->count());
        $this->ke($u, '/umkm/penilaian-ulang')->assertSee('Anda sudah mengisi penilaian');
    }

    public function test_new_period_allows_a_new_submission(): void
    {
        $lama = $this->buatPeriode(['status' => 'ditutup']);
        $u = $this->umkmAktif();
        PenilaianLevel::create([
            'umkm_id' => $u->umkm->id, 'periode_id' => $lama->id, 'jenis' => 'ulang',
            'tanggal_penilaian' => now()->subYear()->toDateString(), 'total_skor' => 5,
            'level_rekomendasi_id' => $this->levelKode('L1')->id, 'arah_rekomendasi' => 'tetap',
            'status_peninjauan' => 'selesai',
        ]);

        $this->buatPeriode(['nama' => 'Periode Baru']);

        $this->kirimUlang($u, 'abab')->assertSessionHas('success');
        $this->assertSame(2, PenilaianLevel::where('umkm_id', $u->umkm->id)->where('jenis', 'ulang')->count());
    }

    public function test_incomplete_answers_are_rejected(): void
    {
        $this->buatPeriode();
        $u = $this->umkmAktif();
        $jawaban = $this->jawab('abab');
        array_pop($jawaban);

        $this->sebagai($u)->post(route('umkm.penilaian-ulang.store'), ['jawaban' => $jawaban])->assertSessionHasErrors('jawaban');
        $this->sebagai($u)->post(route('umkm.penilaian-ulang.store'), [])->assertSessionHasErrors('jawaban');

        $this->assertSame(0, PenilaianLevel::where('jenis', 'ulang')->count());
    }

    // ---------- FR-23: rekomendasi dan arah ----------

    public function test_submission_stores_waiting_review_and_does_not_change_level(): void
    {
        $periode = $this->buatPeriode();
        $u = $this->umkmAktif([], 'L3');

        $this->kirimUlang($u, 'aaaa')->assertSessionHas('success'); // total 4 -> Dasar

        $n = $this->ulangDari($u);
        $this->assertSame('ulang', $n->jenis);
        $this->assertSame($periode->id, $n->periode_id);
        $this->assertSame('menunggu', $n->status_peninjauan);
        $this->assertSame(4, $n->total_skor);
        $this->assertSame($this->levelKode('L1')->id, $n->level_rekomendasi_id);
        $this->assertNull($n->level_akhir_id);
        $this->assertSame(4, $n->detail()->count());

        // BR-01: level tidak berubah otomatis.
        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id);
    }

    public function test_direction_is_up_same_or_down_compared_to_current_level(): void
    {
        $this->buatPeriode();

        $turun = $this->umkmAktif([], 'L2');
        $this->kirimUlang($turun, 'aaaa');                      // rekomendasi Dasar
        $this->assertSame('turun', $this->ulangDari($turun)->arah_rekomendasi);

        $naik = $this->umkmAktif([], 'L2');
        $this->kirimUlang($naik, 'cccc');                       // rekomendasi Lanjutan
        $this->assertSame('naik', $this->ulangDari($naik)->arah_rekomendasi);

        $tetap = $this->umkmAktif([], 'L2');
        $this->kirimUlang($tetap, 'aabc');                      // 7 -> Berkembang
        $this->assertSame('tetap', $this->ulangDari($tetap)->arah_rekomendasi);

        foreach ([$turun, $naik, $tetap] as $u) { // tidak ada yang berubah otomatis
            $this->assertSame($this->levelKode('L2')->id, $u->umkm->fresh()->level_id);
        }
    }

    public function test_umkm_without_level_gets_direction_new(): void
    {
        $this->buatPeriode();
        $u = $this->umkmAktif(['level_id' => null]);

        $this->kirimUlang($u, 'abab');

        $this->assertSame('baru', $this->ulangDari($u)->arah_rekomendasi);
    }

    public function test_score_comes_from_chosen_option_not_from_system_suggestion(): void
    {
        $this->buatPeriode();
        $u = $this->umkmAktif([], 'L2');
        $this->catatJurnal($u->umkm->id, now()->subDays(5)->toDateString(), '400', 0, 700_000_000); // saran: omzet b

        $this->kirimUlang($u, 'aaaa'); // UMKM memilih a

        $n = $this->ulangDari($u);
        $this->assertSame(4, $n->total_skor);                       // bukan 5
        $this->assertSame($this->pilihanId(1, 'b'), $n->data_sistem['omzet']['pilihan_disarankan']);
    }

    public function test_system_figures_are_recorded_with_the_submission(): void
    {
        $this->buatPeriode();
        $u = $this->umkmAktif([], 'L2');
        $this->catatJurnal($u->umkm->id, now()->subDays(9)->toDateString(), '400', 0, 600_000_000);

        $this->kirimUlang($u, 'bbbb');

        $rekam = $this->ulangDari($u)->data_sistem;
        $this->assertSame(10, $rekam['hari_data']);
        $this->assertEqualsWithDelta(600_000_000, $rekam['omzet']['nilai'], 0.01);
        $this->assertSame($this->pilihanId(1, 'b'), $rekam['omzet']['pilihan_disarankan']);
        $this->assertArrayHasKey('modal', $rekam);
    }

    public function test_result_page_lists_the_new_assessment_as_waiting(): void
    {
        $this->buatPeriode();
        $u = $this->umkmAktif([], 'L2');
        $this->kirimUlang($u, 'aaaa');

        $this->ke($u, '/umkm/penilaian')->assertOk()
            ->assertSee('Penilaian ulang')
            ->assertSee('Arah rekomendasi dibanding level Anda saat itu')
            ->assertSee('Turun')
            ->assertSee('Level akhir belum ditetapkan Admin');
    }

    public function test_only_umkm_can_use_the_form(): void
    {
        $this->buatPeriode();

        $this->get('/umkm/penilaian-ulang')->assertRedirect(route('login'));
        $this->sebagai($this->buatAdminK3())->get('/umkm/penilaian-ulang')->assertForbidden();
        $this->sebagai($this->buatAdminK3())->post('/umkm/penilaian-ulang', ['jawaban' => $this->jawab('abab')])->assertForbidden();
    }
}
