<?php

namespace Tests\Feature;

use App\Models\PilihanJawaban;
use App\Models\UmkmLevel;
use App\Services\PenilaianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/**
 * FR-04: skor, total, dan level rekomendasi dihitung dari tabel (BR-09, BR-12).
 */
class PenilaianServiceTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedK1();
    }

    private function hitung(string $huruf): array
    {
        return app(PenilaianService::class)->hitung($this->jawab($huruf));
    }

    public static function contohKriteriaUji(): array
    {
        return [
            'semua a'  => ['aaaa', 4, 'L1'],
            'a,a,a,c'  => ['aaac', 6, 'L1'],
            'a,a,b,c'  => ['aabc', 7, 'L2'],
            'c,b,b,b'  => ['cbbb', 9, 'L2'],
            'c,c,b,b'  => ['ccbb', 10, 'L3'],
            'semua c'  => ['cccc', 12, 'L3'],
        ];
    }

    #[DataProvider('contohKriteriaUji')]
    public function test_total_and_level_match_acceptance_examples(string $huruf, int $total, string $kodeLevel): void
    {
        $hasil = $this->hitung($huruf);

        $this->assertSame($total, $hasil['total']);
        $this->assertSame($kodeLevel, $hasil['level']->kode);
        $this->assertCount(4, $hasil['detail']);
    }

    public function test_option_d_scores_three_and_flags_admin_decision(): void
    {
        foreach (['daaa', 'adaa', 'aada'] as $huruf) {
            $hasil = $this->hitung($huruf);
            $this->assertTrue($hasil['perlu_keputusan_admin'], "pilihan d pada {$huruf}");
            $this->assertSame(6, $hasil['total']); // 3 + 1 + 1 + 1
        }

        $this->assertFalse($this->hitung('cccc')['perlu_keputusan_admin']);
    }

    public function test_changing_option_score_changes_result_without_code_change(): void
    {
        $this->assertSame('L1', $this->hitung('aaaa')['level']->kode);

        // Pilihan a Pertanyaan 1 dinaikkan ke skor 3 => total 6, masih Dasar.
        // Lalu pilihan a Pertanyaan 2 juga 3 => total 8 => Berkembang.
        foreach ([1, 2] as $urutan) {
            PilihanJawaban::whereHas('indikator', fn ($q) => $q->where('urutan', $urutan))
                ->where('urutan', 1)->update(['skor' => 3]);
        }

        $hasil = $this->hitung('aaaa');
        $this->assertSame(8, $hasil['total']);
        $this->assertSame('L2', $hasil['level']->kode);
    }

    public function test_changing_level_boundaries_changes_result(): void
    {
        UmkmLevel::where('kode', 'L1')->update(['skor_max' => 4]);
        UmkmLevel::where('kode', 'L2')->update(['skor_min' => 5]);

        $this->assertSame('L2', $this->hitung('aaac')['level']->kode); // total 6
    }

    public function test_unanswered_question_is_rejected(): void
    {
        $jawaban = $this->jawab('aaaa');
        array_pop($jawaban);

        $this->expectException(InvalidArgumentException::class);
        app(PenilaianService::class)->hitung($jawaban);
    }

    public function test_option_from_another_question_is_rejected(): void
    {
        $jawaban = $this->jawab('aaaa');
        $ids = array_keys($jawaban);
        $jawaban[$ids[0]] = $jawaban[$ids[1]]; // pilihan milik Pertanyaan 2 untuk Pertanyaan 1

        $this->expectException(InvalidArgumentException::class);
        app(PenilaianService::class)->hitung($jawaban);
    }
}
