<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\JurnalUmum;
use App\Models\Piutang;
use App\Models\PiutangPembayaran;
use App\Models\Umkm;
use App\Services\AccountingService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Demo\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jurnal otomatis memakai kode akun COA bawaan (113 piutang, 114 persediaan bahan,
 * 311 modal, 501 HPP) dan halaman Laporan tampil saat sudah ada transaksi.
 */
class KodeAkunJurnalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['minect.initial_password' => 'sandi-uji-123']);
        $this->travelTo('2026-11-26 10:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoSeeder::class);
    }

    private function umkm(string $nama = 'Keripik Contoh Melati'): Umkm
    {
        return Umkm::where('nama_usaha', $nama)->firstOrFail();
    }

    /** Setiap baris jurnal otomatis harus cocok dengan nama akun di COA UMKM itu. */
    private function assertSesuaiCoa(Umkm $umkm): void
    {
        $coa = Coa::where('umkm_id', $umkm->id)->pluck('nama_akun', 'kode_akun');

        foreach (JurnalUmum::where('umkm_id', $umkm->id)->get() as $j) {
            // Akun beban (6xx) dan pembelian periodik (510) dibuat saat dipakai.
            if (isset($coa[$j->kode_akun])) {
                $this->assertSame($coa[$j->kode_akun], $j->nama_akun, "akun {$j->kode_akun} ({$j->ref_tipe})");
            }
        }
    }

    public function test_credit_sale_goes_to_receivable_and_opening_stock_to_owner_equity(): void
    {
        $u = $this->umkm();
        $piutang = Piutang::where('umkm_id', $u->id)->firstOrFail();

        $baris = JurnalUmum::where('umkm_id', $u->id)->where('ref_tipe', 'penjualan')
            ->where('ref_id', $piutang->penjualan_id)->where('debit', '>', 0)->firstOrFail();
        $this->assertSame('113', $baris->kode_akun);
        $this->assertSame('Piutang Usaha', $baris->nama_akun);

        $modal = JurnalUmum::where('umkm_id', $u->id)->where('ref_tipe', 'saldo_awal_bahan')
            ->where('kredit', '>', 0)->pluck('kode_akun')->unique()->all();
        $this->assertSame(['311'], array_values($modal));

        $this->assertSesuaiCoa($u);
    }

    public function test_receivable_payment_reduces_receivable_account(): void
    {
        $u = $this->umkm();
        $piutang = Piutang::where('umkm_id', $u->id)->firstOrFail();
        $bayar = PiutangPembayaran::create([
            'piutang_id' => $piutang->id, 'tanggal_bayar' => '2026-11-26',
            'jumlah_bayar' => 50000, 'metode_bayar' => 'tunai',
        ]);

        app(AccountingService::class)->jurnalPembayaranPiutang($u, $bayar);

        $saldo = JurnalUmum::where('umkm_id', $u->id)->where('kode_akun', '113')->get();
        $this->assertEqualsWithDelta((float) $piutang->nominal_awal - 50000, $saldo->sum('debit') - $saldo->sum('kredit'), 0.01);
    }

    public function test_perpetual_cost_of_goods_uses_501_and_material_inventory(): void
    {
        $u = $this->umkm();
        $u->update(['recording_method' => 'perpetual']);
        $penjualan = $u->penjualan()->firstOrFail();

        app(AccountingService::class)->jurnalPenjualan($u->fresh(), $penjualan, 100000, 40000, false);

        $baris = JurnalUmum::where('ref_tipe', 'penjualan')->where('ref_id', $penjualan->id)->get();
        $this->assertSame(40000.0, (float) $baris->where('kode_akun', '501')->sum('debit'));
        $this->assertSame(40000.0, (float) $baris->where('kode_akun', '114')->sum('kredit'));
        $this->assertSame(0, $baris->whereIn('kode_akun', ['113', '500'])->count());
    }

    public function test_report_page_opens_with_transactions_for_every_demo_umkm(): void
    {
        foreach (['Keripik Contoh Melati', 'Bengkel Contoh Jaya', 'Pangkas Contoh Rapi'] as $nama) {
            $u = $this->umkm($nama);

            $this->actingAs($u->user->fresh())->get('/umkm/laporan?bulan=2026-11')
                ->assertOk()
                ->assertSee('25/11/2026', false)->assertSee('Jurnal Umum');
        }
    }
}
