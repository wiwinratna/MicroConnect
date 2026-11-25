<?php

namespace Tests\Feature;

use App\Models\Indikator;
use App\Services\DataSistemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\SiapkanPenilaianUlang;
use Tests\TestCase;

/**
 * Angka dari pembukuan untuk pengisian bawaan penilaian ulang: omzet 12 bulan, modal, hari data,
 * dan pencocokan angka ke pilihan jawaban.
 */
class DataSistemServiceTest extends TestCase
{
    use RefreshDatabase, SiapkanPenilaianUlang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK4b();
    }

    private function hitung(\App\Models\Umkm $umkm): array
    {
        return app(DataSistemService::class)->hitung($umkm);
    }

    public function test_no_bookkeeping_data_gives_no_figures(): void
    {
        $u = $this->umkmAktif();

        $hasil = $this->hitung($u->umkm);

        $this->assertSame(0, $hasil['hari_data']);
        $this->assertNull($hasil['omzet']);
        $this->assertNull($hasil['modal']);
    }

    public function test_turnover_is_total_income_of_last_twelve_months_only(): void
    {
        $u = $this->umkmAktif();
        $id = $u->umkm->id;

        $this->catatJurnal($id, now()->subDays(10)->toDateString(), '400', 0, 600_000_000);
        $this->catatJurnal($id, now()->subDays(5)->toDateString(), '400', 1_000_000, 0);       // retur mengurangi
        $this->catatJurnal($id, now()->subMonths(13)->toDateString(), '400', 0, 900_000_000);  // di luar 12 bulan
        $this->catatJurnal($id, now()->subDays(3)->toDateString(), '111', 50_000_000, 0);       // bukan pendapatan

        $this->assertEqualsWithDelta(599_000_000, $this->hitung($u->umkm)['omzet'], 0.01);
    }

    public function test_capital_is_equity_balance_plus_cumulative_profit_after_drawings(): void
    {
        $u = $this->umkmAktif();
        $id = $u->umkm->id;
        $t = now()->subDays(20)->toDateString();

        $this->catatJurnal($id, $t, '311', 0, 50_000_000);   // modal pemilik
        $this->catatJurnal($id, $t, '312', 5_000_000, 0);    // prive
        $this->catatJurnal($id, $t, '400', 0, 20_000_000);   // pendapatan
        $this->catatJurnal($id, $t, '510', 8_000_000, 0);    // hpp
        $this->catatJurnal($id, $t, '610', 2_000_000, 0);    // beban

        // (50 - 5) + (20 - 8 - 2) = 55 juta
        $this->assertEqualsWithDelta(55_000_000, $this->hitung($u->umkm)['modal'], 0.01);
    }

    public function test_days_of_data_are_counted_and_capped_at_one_year(): void
    {
        $a = $this->umkmAktif();
        $this->catatJurnal($a->umkm->id, now()->subDays(10)->toDateString(), '400', 0, 1000);
        $this->assertSame(11, $this->hitung($a->umkm)['hari_data']);

        $b = $this->umkmAktif();
        $this->catatJurnal($b->umkm->id, now()->subDays(500)->toDateString(), '400', 0, 1000);
        $this->assertSame(365, $this->hitung($b->umkm)['hari_data']);
    }

    public function test_figures_are_not_projected_when_data_is_short(): void
    {
        $u = $this->umkmAktif();
        $this->catatJurnal($u->umkm->id, now()->subDays(2)->toDateString(), '400', 0, 1_000_000);

        $hasil = $this->hitung($u->umkm);

        $this->assertSame(3, $hasil['hari_data']);
        $this->assertEqualsWithDelta(1_000_000, $hasil['omzet'], 0.01); // apa adanya, tanpa dikali setahun
    }

    public function test_other_umkm_data_is_not_mixed_in(): void
    {
        $a = $this->umkmAktif();
        $b = $this->umkmAktif();
        $this->catatJurnal($b->umkm->id, now()->subDays(1)->toDateString(), '400', 0, 700_000_000);

        $this->assertNull($this->hitung($a->umkm)['omzet']);
    }

    // ---------- pencocokan angka ke pilihan (rentang Tabel 3.6) ----------

    public static function batasOmzet(): array
    {
        return [
            'nol'                  => [0, 'a'],
            'negatif'              => [-5_000_000, 'a'],
            'tepat 500 juta'       => [500_000_000, 'a'],
            'lewat 500 juta'       => [500_000_001, 'b'],
            'tepat 1,2 miliar'     => [1_200_000_000, 'b'],
            'lewat 1,2 miliar'     => [1_200_000_001, 'c'],
            'tepat 2 miliar'       => [2_000_000_000, 'c'],
            'lewat 2 miliar'       => [2_000_000_001, 'd'],
        ];
    }

    #[DataProvider('batasOmzet')]
    public function test_turnover_maps_to_the_right_option(float $nilai, string $huruf): void
    {
        $ind = Indikator::where('urutan', 1)->with('pilihan')->first();

        $this->assertSame(
            $this->pilihanId(1, $huruf),
            app(DataSistemService::class)->pilihanUntuk($ind, $nilai)->id
        );
    }

    public static function batasModal(): array
    {
        return [
            'tepat 100 juta' => [100_000_000, 'a'],
            'lewat 100 juta' => [100_000_001, 'b'],
            'tepat 500 juta' => [500_000_000, 'b'],
            'lewat 500 juta' => [500_000_001, 'c'],
            'tepat 1 miliar' => [1_000_000_000, 'c'],
            'lewat 1 miliar' => [1_000_000_001, 'd'],
        ];
    }

    #[DataProvider('batasModal')]
    public function test_capital_maps_to_the_right_option(float $nilai, string $huruf): void
    {
        $ind = Indikator::where('urutan', 2)->with('pilihan')->first();

        $this->assertSame(
            $this->pilihanId(2, $huruf),
            app(DataSistemService::class)->pilihanUntuk($ind, $nilai)->id
        );
    }

    public function test_only_turnover_and_capital_questions_are_filled_from_system(): void
    {
        $layanan = app(DataSistemService::class);
        $jenis = Indikator::orderBy('urutan')->get()->map(fn ($i) => $layanan->jenisSistem($i))->all();

        $this->assertSame(['omzet', 'modal', null, null], $jenis);
    }
}
