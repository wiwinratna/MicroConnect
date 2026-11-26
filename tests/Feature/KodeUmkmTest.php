<?php

namespace Tests\Feature;

use App\Models\Umkm;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Kode UMKM baru selalu melanjutkan nomor terbesar. */
class KodeUmkmTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    public function test_first_code_starts_at_one(): void
    {
        $this->assertSame('UM00001', Umkm::getKodeUmkm());
    }

    public function test_next_code_follows_highest_number_even_with_equal_or_older_timestamps(): void
    {
        $a = $this->buatUmkm(['kode_umkm' => 'UM00001']);
        $b = $this->buatUmkm(['kode_umkm' => 'UM00007']);

        // Nomor terbesar justru tercatat lebih lama.
        DB::table('umkm')->where('id', $a->umkm->id)->update(['created_at' => '2026-11-26 10:00:00']);
        DB::table('umkm')->where('id', $b->umkm->id)->update(['created_at' => '2026-11-25 10:00:00']);

        $this->assertSame('UM00008', Umkm::getKodeUmkm());
    }

    public function test_database_seeder_runs_on_empty_database(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kode = Umkm::pluck('kode_umkm');
        $this->assertGreaterThanOrEqual(3, $kode->count());
        $this->assertSame($kode->count(), $kode->unique()->count());
    }
}
