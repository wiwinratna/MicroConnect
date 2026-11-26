<?php

namespace Tests\Feature;

use App\Models\IuranBulanan;
use App\Models\IuranPeriode;
use App\Services\IuranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** BR-15: iuran mengikuti level. Fitur Produksi nonaktif (MVP). */
class IuranProduksiTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-26 10:00:00');
        $this->siapkanK3();
    }

    private function periode(int $nominal = 50000): IuranPeriode
    {
        return IuranPeriode::create([
            'periode' => '2026-11', 'nominal_default' => $nominal, 'jatuh_tempo' => '2026-11-30',
            'status' => 'terbit', 'created_by' => $this->buatAdminK3()->id,
        ]);
    }

    private function nominal(int $umkmId): ?float
    {
        $n = IuranBulanan::where('umkm_id', $umkmId)->where('periode', '2026-11')->value('nominal');

        return $n === null ? null : (float) $n;
    }

    public function test_bill_amount_follows_level_without_master_period(): void
    {
        foreach (['L2' => 20000.0, 'L3' => 30000.0] as $level => $harapan) {
            $u = $this->buatUmkm([], $level);

            app(IuranService::class)->getOrCreate($u->umkm->id);

            $this->assertSame($harapan, $this->nominal($u->umkm->id), $level);
        }
    }

    public function test_free_level_gets_no_bill_and_no_reminder(): void
    {
        $u = $this->buatUmkm([], 'L1');

        $this->sebagai($u)->get('/umkm/dashboard')->assertOk()->assertDontSee('Iuran Aplikasi Menunggu');
        $this->sebagai($u)->get('/umkm/iuran')->assertOk();

        $this->assertNull($this->nominal($u->umkm->id));
    }

    public function test_period_generation_uses_level_and_fallback_only_without_level(): void
    {
        $l1 = $this->buatUmkm([], 'L1');
        $l2 = $this->buatUmkm([], 'L2');
        $l3 = $this->buatUmkm([], 'L3');
        $tanpa = $this->buatUmkm(['level_id' => null]);

        $jumlah = app(IuranService::class)->generateFromPeriode($this->periode(45000));

        $this->assertSame(3, $jumlah);
        $this->assertNull($this->nominal($l1->umkm->id));
        $this->assertSame(20000.0, $this->nominal($l2->umkm->id));
        $this->assertSame(30000.0, $this->nominal($l3->umkm->id));
        $this->assertSame(45000.0, $this->nominal($tanpa->umkm->id));
    }

    public function test_existing_bill_is_not_changed(): void
    {
        $u = $this->buatUmkm([], 'L1');
        IuranBulanan::create([
            'umkm_id' => $u->umkm->id, 'periode' => '2026-11', 'nominal' => 50000,
            'status' => 'belum_bayar', 'jatuh_tempo' => '2026-11-30',
        ]);

        app(IuranService::class)->getOrCreate($u->umkm->id);
        app(IuranService::class)->generateMonthly('2026-11');

        $this->assertSame(50000.0, $this->nominal($u->umkm->id));
        $this->assertSame(1, IuranBulanan::count());
    }

    public function test_production_pages_are_closed(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'barang', 'memiliki_persediaan' => true, 'melakukan_produksi' => true], 'L3');

        $this->assertDiblokir($u, '/umkm/produksi');
        $this->assertDiblokir($u, '/umkm/produksi/create');
    }
}
