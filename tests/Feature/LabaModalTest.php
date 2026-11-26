<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\JurnalUmum;
use App\Models\StokMutasi;
use App\Models\User;
use App\Services\DataSistemService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * Laba Rugi dan Perubahan Modal dengan angka yang bisa dihitung tangan.
 *
 * Oktober : setor 1.000.000; jual 500.000; gaji (503) 100.000; sewa (612) 50.000.
 *           Laba 350.000. Modal akhir 1.350.000.
 * November: setor 200.000; prive (312) 80.000; saldo awal bahan 10 kg x 5.000 = 50.000;
 *           beli 100.000; stok akhir 70.000; bahan langsung (502) 20.000;
 *           jual 600.000; listrik (505) 40.000.
 *           HPP = 0 + 100.000 + 50.000 + 20.000 - 70.000 = 100.000.
 *           Setoran = 200.000 + 50.000 (saldo awal bahan) = 250.000.
 *           Laba = 600.000 - 100.000 - 40.000 = 460.000.
 *           Modal akhir = 1.350.000 + 250.000 + 460.000 - 80.000 = 1.980.000.
 */
class LabaModalTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-26 10:00:00');
        $this->siapkanK3();
        $this->user = $this->buatUmkm(['recording_method' => 'periodik', 'inventory_method' => 'Average'], 'L3');
        $this->isiData();
    }

    private function jurnal(string $tanggal, string $kode, float $debit, float $kredit, string $tipe = 'manual', ?int $ref = null): void
    {
        JurnalUmum::create([
            'umkm_id' => $this->user->umkm->id, 'tanggal' => $tanggal, 'kode_akun' => $kode,
            'nama_akun' => "Akun {$kode}", 'keterangan' => 'uji', 'debit' => $debit, 'kredit' => $kredit,
            'ref_tipe' => $tipe, 'ref_id' => $ref,
        ]);
    }

    /** Dua baris seimbang. */
    private function transaksi(string $tanggal, string $debit, string $kredit, float $nilai): void
    {
        $this->jurnal($tanggal, $debit, $nilai, 0);
        $this->jurnal($tanggal, $kredit, 0, $nilai);
    }

    private function isiData(): void
    {
        $this->transaksi('2026-10-01', '111', '311', 1000000);
        $this->transaksi('2026-10-10', '111', '400', 500000);
        $this->transaksi('2026-10-20', '503', '111', 100000);
        $this->transaksi('2026-10-21', '612', '111', 50000);

        $umkmId = $this->user->umkm->id;
        $bahan = BahanBaku::create([
            'kode_bahan' => 'BB00001', 'umkm_id' => $umkmId, 'nama_bahan' => 'Tepung', 'satuan' => 'kg', 'stok_awal' => 0,
        ]);
        $mutasi = fn (string $tanggal, string $jenis, float $qty, string $tipe) => StokMutasi::create([
            'umkm_id' => $umkmId, 'bahan_id' => $bahan->id, 'tanggal' => $tanggal, 'jenis' => $jenis,
            'qty' => $qty, 'harga_unit' => 5000, 'ref_tipe' => $tipe, 'ref_id' => $bahan->id,
        ]);
        // Masuk 10 + 20 kg, keluar 16 kg: sisa 14 kg x 5.000 = 70.000.
        $mutasi('2026-11-01', 'MASUK', 10, 'saldo_awal');
        $mutasi('2026-11-03', 'MASUK', 20, 'pembelian');
        $mutasi('2026-11-10', 'KELUAR', 16, 'penjualan');

        $this->transaksi('2026-11-01', '114', '311', 50000);
        $this->transaksi('2026-11-02', '111', '311', 200000);
        $this->transaksi('2026-11-03', '510', '111', 100000);
        $this->transaksi('2026-11-05', '502', '111', 20000);
        $this->transaksi('2026-11-10', '111', '400', 600000);
        $this->transaksi('2026-11-15', '312', '111', 80000);
        $this->transaksi('2026-11-25', '505', '111', 40000);
    }

    private function laporan(string $bulan)
    {
        return $this->sebagai($this->user)->get('/umkm/laporan?bulan=' . $bulan)->assertOk();
    }

    private function assertAngka($respons, array $harapan): void
    {
        foreach ($harapan as $kunci => $nilai) {
            $this->assertEqualsWithDelta($nilai, (float) $respons->viewData($kunci), 0.01, $kunci);
        }
    }

    public function test_first_month_profit_and_equity(): void
    {
        $this->assertAngka($this->laporan('2026-10'), [
            'totalHpp' => 0, 'totalBeban' => 150000, 'labaBersih' => 350000,
            'modalAwal' => 0, 'setoranModal' => 1000000, 'prive' => 0, 'modalAkhir' => 1350000,
        ]);
    }

    public function test_second_month_carries_profit_and_counts_capital_prive_and_opening_stock(): void
    {
        $this->assertAngka($this->laporan('2026-11'), [
            'saldoAwalBahan' => 50000, 'bahanLangsung' => 20000, 'totalHpp' => 100000,
            'totalBeban' => 40000, 'labaBersih' => 460000,
            'modalAwal' => 1350000, 'setoranModal' => 250000, 'prive' => 80000, 'modalAkhir' => 1980000,
        ]);
    }

    public function test_perpetual_method_reads_cost_from_journal_and_salary_as_expense(): void
    {
        $this->user->umkm->update(['recording_method' => 'perpetual']);
        $this->transaksi('2026-11-20', '501', '114', 90000);

        // HPP = 501 90.000 + 502 20.000 + 510 100.000 (akun 5xx selain 503-508).
        $this->assertAngka($this->laporan('2026-11'), ['totalHpp' => 210000, 'totalBeban' => 40000]);
    }

    public function test_reassessment_capital_matches_equity_report(): void
    {
        $data = app(DataSistemService::class)->hitung($this->user->umkm->fresh(), Carbon::parse('2026-11-26'));

        $this->assertEqualsWithDelta(1980000, $data['modal'], 0.01);
    }

    public function test_report_shows_capital_line_and_pdf_exports_work(): void
    {
        $this->laporan('2026-11')->assertSee('Setoran Modal')->assertSee('Saldo Awal Bahan');

        foreach (['laba-rugi', 'perubahan-modal'] as $jenis) {
            $nama = 'umkm.export.' . str_replace('-', '_', $jenis);
            $this->sebagai($this->user)->get(route($nama, ['bulan' => '2026-11', 'format' => 'pdf']))->assertOk();
        }
    }
}
