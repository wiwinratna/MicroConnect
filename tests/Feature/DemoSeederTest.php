<?php

namespace Tests\Feature;

use App\Models\JurnalUmum;
use App\Models\Penjualan;
use App\Models\PengajuanPendaftaran;
use App\Models\Umkm;
use App\Services\InventoryService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Demo\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** K5: data demo lengkap, konsisten, dan aman dijalankan ulang. */
class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['minect.initial_password' => 'sandi-uji-123']);
        $this->travelTo('2026-11-26 10:00:00');
        $this->seed(DatabaseSeeder::class);
    }

    private function demo(string $usaha): Umkm
    {
        return Umkm::where('nama_usaha', $usaha)->firstOrFail();
    }

    public function test_creates_three_active_umkm_with_expected_profile_and_level(): void
    {
        $this->seed(DemoSeeder::class);

        $harapan = [
            'Keripik Contoh Melati' => ['barang', true, true, 'L3'],
            'Bengkel Contoh Jaya'   => ['jasa', true, false, 'L2'],
            'Pangkas Contoh Rapi'   => ['jasa', false, false, 'L1'],
        ];

        foreach ($harapan as $nama => [$jenis, $persediaan, $produksi, $level]) {
            $u = $this->demo($nama);
            $this->assertSame('aktif', $u->status);
            $this->assertSame($jenis, $u->rumpun);
            $this->assertSame($persediaan, (bool) $u->memiliki_persediaan);
            $this->assertSame($produksi, (bool) $u->melakukan_produksi);
            $this->assertSame($level, $u->level->kode);
        }

        $menunggu = PengajuanPendaftaran::where('status', 'menunggu')->get();
        $this->assertCount(1, $menunggu);
        $this->assertSame('Tenun Contoh Lestari', $menunggu->first()->nama_usaha);
    }

    public function test_transactions_cover_three_months_with_balanced_journals_and_stock(): void
    {
        $this->seed(DemoSeeder::class);

        foreach (['Keripik Contoh Melati', 'Bengkel Contoh Jaya', 'Pangkas Contoh Rapi'] as $nama) {
            $u = $this->demo($nama);

            $bulan = Penjualan::where('umkm_id', $u->id)->pluck('tanggal')
                ->map(fn ($t) => substr((string) $t, 0, 7))->unique()->sort()->values()->all();
            $this->assertSame(['2026-09', '2026-10', '2026-11'], $bulan, $nama);

            $jurnal = JurnalUmum::where('umkm_id', $u->id);
            $this->assertGreaterThan(0, (float) $jurnal->sum('debit'));
            $this->assertEqualsWithDelta((float) $jurnal->sum('debit'), (float) $jurnal->sum('kredit'), 0.01, $nama);

            $this->assertSame(0, JurnalUmum::where('umkm_id', $u->id)->where('tanggal', '>', '2026-11-26')->count());

            foreach (DB::table('bahan_baku')->where('umkm_id', $u->id)->pluck('id') as $bahanId) {
                $this->assertGreaterThanOrEqual(0, app(InventoryService::class)->getStok($u->id, 'bahan', $bahanId));
            }
        }

        $this->assertSame(1, DB::table('piutang')->count());
    }

    public function test_running_twice_does_not_duplicate_data(): void
    {
        $this->seed(DemoSeeder::class);
        $jumlah = fn () => [Umkm::count(), Penjualan::count(), JurnalUmum::count(), PengajuanPendaftaran::count()];
        $awal = $jumlah();

        $this->seed(DemoSeeder::class);

        $this->assertSame($awal, $jumlah());
    }

    public function test_works_early_in_the_month_without_future_dates(): void
    {
        $this->travelTo('2026-12-02 10:00:00');

        $this->seed(DemoSeeder::class);

        $this->assertSame(0, JurnalUmum::where('tanggal', '>', '2026-12-02')->count());
    }

    public function test_default_seeder_does_not_include_demo_data(): void
    {
        $this->assertSame(0, Umkm::where('nama_usaha', 'like', '%Contoh%')->count());
    }
}
