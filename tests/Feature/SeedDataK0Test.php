<?php

namespace Tests\Feature;

use Database\Seeders\IndikatorSeeder;
use Database\Seeders\JenisUsahaSeeder;
use Database\Seeders\ModulSeeder;
use Database\Seeders\UmkmLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * K0: data awal (Tabel 3.6, 3.7, 3.8, batas skor level). BR-09, BR-11, BR-12.
 */
class SeedDataK0Test extends TestCase
{
    use RefreshDatabase;

    private function seedAll(): void
    {
        $this->seed(UmkmLevelSeeder::class);
        $this->seed(JenisUsahaSeeder::class);
        $this->seed(ModulSeeder::class);
        $this->seed(IndikatorSeeder::class);
    }

    public function test_level_score_ranges_are_stored_in_database(): void
    {
        $this->seedAll();

        $levels = DB::table('umkm_level')->orderBy('urutan')->get(['kode', 'nama_level', 'skor_min', 'skor_max']);

        $this->assertCount(3, $levels);
        $this->assertSame([4, 6], [(int) $levels[0]->skor_min, (int) $levels[0]->skor_max]);
        $this->assertSame([7, 9], [(int) $levels[1]->skor_min, (int) $levels[1]->skor_max]);
        $this->assertSame([10, 12], [(int) $levels[2]->skor_min, (int) $levels[2]->skor_max]);
        $this->assertSame('Level 2 - Berkembang', $levels[1]->nama_level);
    }

    public function test_questionnaire_has_four_indicators_and_scores_one_to_three(): void
    {
        $this->seedAll();

        $this->assertSame(4, DB::table('indikator')->count());
        $this->assertSame(15, DB::table('pilihan_jawaban')->count());

        // Skor 1-3 dan total maksimum 12 (4 x 3).
        $max = DB::table('pilihan_jawaban')->selectRaw('indikator_id, max(skor) as m')
            ->groupBy('indikator_id')->pluck('m');
        $this->assertSame(12, (int) $max->sum());
        $this->assertSame(1, (int) DB::table('pilihan_jawaban')->min('skor'));
    }

    public function test_option_d_on_questions_1_to_3_is_flagged_outside_micro_limit(): void
    {
        $this->seedAll();

        $flagged = DB::table('pilihan_jawaban')
            ->join('indikator', 'indikator.id', '=', 'pilihan_jawaban.indikator_id')
            ->where('di_luar_batas_mikro', true)
            ->orderBy('indikator.urutan')
            ->get(['indikator.urutan', 'pilihan_jawaban.skor']);

        $this->assertSame([1, 2, 3], $flagged->pluck('urutan')->map(fn ($v) => (int) $v)->all());
        $this->assertSame([3, 3, 3], $flagged->pluck('skor')->map(fn ($v) => (int) $v)->all());
    }

    public function test_question_four_options_carry_examples(): void
    {
        $this->seedAll();

        $q4 = DB::table('indikator')->where('urutan', 4)->value('id');
        $this->assertSame(3, DB::table('pilihan_jawaban')->where('indikator_id', $q4)->whereNotNull('contoh')->count());
    }

    public function test_business_types_and_module_rules_match_tables_3_7_and_3_8(): void
    {
        $this->seedAll();

        $this->assertSame(7, DB::table('jenis_usaha')->count());
        $this->assertSame(1, DB::table('jenis_usaha')->where('rumpun_bawaan', 'ditentukan_umkm')->count());
        $this->assertSame(1, DB::table('jenis_usaha')->where('rumpun_bawaan', 'jasa')->count());

        $this->assertSame(9, DB::table('modul')->count());
        $this->assertSame(18, DB::table('aturan_modul')->count());

        $rule = fn (string $kode, string $rumpun) => DB::table('aturan_modul')
            ->join('modul', 'modul.id', '=', 'aturan_modul.modul_id')
            ->where('modul.kode', $kode)->where('aturan_modul.rumpun', $rumpun)
            ->first(['kondisi', 'label_tampil']);

        $this->assertSame('jika_melakukan_produksi', $rule('bahan_baku', 'barang')->kondisi);
        $this->assertSame('jika_memiliki_persediaan', $rule('bahan_baku', 'jasa')->kondisi);
        $this->assertSame('tidak_ditampilkan', $rule('produksi', 'jasa')->kondisi);
        $this->assertSame('Produk', $rule('produk', 'barang')->label_tampil);
        $this->assertSame('Daftar Layanan Jasa', $rule('produk', 'jasa')->label_tampil);
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seedAll();
        $this->seedAll();

        $this->assertSame(3, DB::table('umkm_level')->count());
        $this->assertSame(7, DB::table('jenis_usaha')->count());
        $this->assertSame(18, DB::table('aturan_modul')->count());
        $this->assertSame(4, DB::table('indikator')->count());
        $this->assertSame(15, DB::table('pilihan_jawaban')->count());
    }
}
