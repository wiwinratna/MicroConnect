<?php

namespace Tests\Feature;

use App\Helpers\FeatureAccess;
use App\Helpers\KatalogLaporan;
use Database\Seeders\UmkmLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * FR-18 dan FR-20: laporan per level (Tabel 3.9) diatur Admin dan dipakai di sisi UMKM.
 */
class LaporanLevelTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    /** Daftar kode laporan yang aktif untuk level tertentu, dilihat dari sisi UMKM. */
    private function laporanAktif(string $kodeLevel): array
    {
        $u = $this->buatUmkm(['rumpun' => null], $kodeLevel);
        $this->sebagai($u); // jadikan pengguna aktif untuk FeatureAccess

        return collect(array_keys(KatalogLaporan::DAFTAR))
            ->filter(fn ($kode) => FeatureAccess::can(KatalogLaporan::DAFTAR[$kode][1][0]))
            ->values()->all();
    }

    private function simpanMatriks(array $perKodeLevel)
    {
        $matriks = [];
        foreach ($perKodeLevel as $kodeLevel => $kodeLaporan) {
            $matriks[$this->levelKode($kodeLevel)->id] = $kodeLaporan;
        }

        return $this->sebagai($this->buatAdminK3())->put(route('admin.laporan-level.update'), ['matriks' => $matriks]);
    }

    // ---------- Isi awal = Tabel 3.9 ----------

    public function test_default_matrix_matches_table_3_9(): void
    {
        $this->assertEqualsCanonicalizing(['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan'], $this->laporanAktif('L1'));

        $this->assertEqualsCanonicalizing(
            ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar', 'laba_rugi', 'kartu_stok', 'piutang'],
            $this->laporanAktif('L2')
        );

        $this->assertEqualsCanonicalizing(array_keys(KatalogLaporan::DAFTAR), $this->laporanAktif('L3'));
    }

    // ---------- FR-20: sisi UMKM ----------

    public function test_basic_level_only_sees_reports_mapped_for_it(): void
    {
        $u = $this->buatUmkm(['rumpun' => null], 'L1');

        $halaman = $this->ke($u, '/umkm/laporan')->assertOk();
        $halaman->assertSee('/umkm/export/jurnal-umum', false);
        $halaman->assertDontSee('/umkm/export/buku-besar', false);
        $halaman->assertDontSee('/umkm/export/laba-rugi', false);
    }

    public function test_higher_levels_see_their_reports_and_not_the_ones_above(): void
    {
        $l2 = $this->buatUmkm(['rumpun' => null], 'L2');
        $halaman = $this->ke($l2, '/umkm/laporan')->assertOk();
        $halaman->assertSee('/umkm/export/buku-besar', false)->assertSee('/umkm/export/laba-rugi', false);
        $halaman->assertDontSee('/umkm/export/arus-kas', false)->assertDontSee('/umkm/export/perubahan-modal', false);

        $l3 = $this->buatUmkm(['rumpun' => null], 'L3');
        $this->ke($l3, '/umkm/laporan')->assertOk()
            ->assertSee('/umkm/export/arus-kas', false)->assertSee('/umkm/export/perubahan-modal', false);
    }

    public function test_report_panels_are_rendered_only_for_allowed_reports(): void
    {
        $hitung = fn (string $kodeLevel) => substr_count(
            $this->ke($this->buatUmkm(['rumpun' => null], $kodeLevel), '/umkm/laporan')->getContent(),
            'class="tab-pane'
        );

        $this->assertSame(3, $hitung('L1'));  // jurnal, pembelian, penjualan
        $this->assertSame(7, $hitung('L2'));  // + buku besar, laba rugi, stok, piutang
        $this->assertSame(9, $hitung('L3'));  // semua laporan
    }

    public function test_basic_level_cannot_open_other_reports_by_url(): void
    {
        $u = $this->buatUmkm(['rumpun' => null], 'L1');

        foreach (['/umkm/export/buku-besar', '/umkm/export/laba-rugi', '/umkm/export/arus-kas', '/umkm/export/perubahan-modal', '/umkm/kartu-stok'] as $url) {
            $this->ke($u, $url)->assertRedirect(route('umkm.dashboard'))->assertSessionHas('error');
        }
    }

    // ---------- FR-18: layar Admin ----------

    public function test_screen_shows_matrix_with_default_checks(): void
    {
        $this->sebagai($this->buatAdminK3())->get(route('admin.laporan-level.index'))
            ->assertOk()
            ->assertSee('Jurnal Umum')->assertSee('Buku Besar')->assertSee('Arus Kas')
            ->assertSee('Level 1 - Dasar')->assertSee('Level 2 - Berkembang')->assertSee('Level 3 - Lanjut');
    }

    public function test_admin_change_affects_umkm_pages_and_urls(): void
    {
        $u = $this->buatUmkm(['rumpun' => null], 'L1');
        $this->ke($u, '/umkm/export/buku-besar')->assertRedirect(route('umkm.dashboard'));

        // Aktifkan Buku Besar untuk Dasar; Berkembang dan Lanjutan tetap.
        $this->simpanMatriks([
            'L1' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar'],
            'L2' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar', 'laba_rugi', 'kartu_stok', 'piutang'],
            'L3' => array_keys(KatalogLaporan::DAFTAR),
        ])->assertRedirect(route('admin.laporan-level.index'));

        $this->ke($u, '/umkm/laporan')->assertSee('/umkm/export/buku-besar', false);
        $this->assertNotSame(route('umkm.dashboard'), $this->ke($u, '/umkm/export/buku-besar')->headers->get('Location'));
    }

    public function test_admin_can_remove_a_report_from_a_level(): void
    {
        $this->simpanMatriks([
            'L1' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan'],
            'L2' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar', 'kartu_stok', 'piutang'], // tanpa laba_rugi
            'L3' => array_keys(KatalogLaporan::DAFTAR),
        ]);

        $u = $this->buatUmkm(['rumpun' => null], 'L2');
        $this->ke($u, '/umkm/export/laba-rugi')->assertRedirect(route('umkm.dashboard'));
    }

    public function test_saving_matrix_leaves_non_report_features_untouched(): void
    {
        $this->simpanMatriks(['L1' => ['jurnal_umum'], 'L2' => [], 'L3' => []]);

        $fitur = (array) $this->levelKode('L1')->fresh()->fitur;

        foreach (['dashboard', 'profile', 'bahan_baku', 'produk', 'pembelian', 'penjualan', 'beban', 'iuran', 'tickets'] as $kunci) {
            $this->assertContains($kunci, $fitur, "fitur {$kunci} hilang");
        }
        $this->assertContains('jurnal_umum', $fitur);
        $this->assertNotContains('laporan_pembelian', $fitur);
    }

    public function test_unknown_report_code_is_rejected(): void
    {
        $this->simpanMatriks(['L1' => ['laporan_siluman']])->assertSessionHasErrors();

        $this->assertNotContains('laporan_siluman', (array) $this->levelKode('L1')->fresh()->fitur);
    }

    public function test_change_is_written_to_audit_log(): void
    {
        $this->simpanMatriks([
            'L1' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar'],
            'L2' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar', 'laba_rugi', 'kartu_stok', 'piutang'],
            'L3' => array_keys(KatalogLaporan::DAFTAR),
        ]);

        $log = DB::table('log_audit')->where('aksi', 'laporan_level.ubah')->get();
        $this->assertCount(1, $log); // hanya Dasar yang berubah
        $this->assertStringContainsString('buku_besar', $log[0]->data_baru);
        $this->assertStringNotContainsString('buku_besar', $log[0]->data_lama);
    }

    public function test_rerunning_seeder_does_not_overwrite_admin_settings(): void
    {
        $this->simpanMatriks([
            'L1' => ['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan', 'buku_besar'],
            'L2' => ['jurnal_umum'], 'L3' => ['jurnal_umum'],
        ]);

        $this->seed(UmkmLevelSeeder::class);

        $this->assertContains('buku_besar', (array) $this->levelKode('L1')->fresh()->fitur);
        $this->assertNotContains('laba_rugi', (array) $this->levelKode('L2')->fresh()->fitur);
    }

    public function test_empty_level_config_falls_back_to_v1_defaults(): void
    {
        DB::table('umkm_level')->where('kode', 'L1')->update(['fitur' => null]);

        $this->assertEqualsCanonicalizing(['jurnal_umum', 'laporan_pembelian', 'laporan_penjualan'], $this->laporanAktif('L1'));
    }

    public function test_saving_matrix_when_level_config_is_empty_starts_from_v1_defaults(): void
    {
        DB::table('umkm_level')->where('kode', 'L1')->update(['fitur' => null]);

        $this->simpanMatriks(['L1' => ['jurnal_umum']]);

        $fitur = (array) $this->levelKode('L1')->fresh()->fitur;
        $this->assertContains('bahan_baku', $fitur); // fitur non-laporan tidak hilang
        $this->assertContains('jurnal_umum', $fitur);
    }

    public function test_admin_user_is_never_limited_by_level_matrix(): void
    {
        $this->sebagai($this->buatAdminK3());

        $this->assertTrue(FeatureAccess::can('arus_kas'));
    }

    public function test_only_admin_can_open_or_change_matrix(): void
    {
        $u = $this->buatUmkm();

        $this->get(route('admin.laporan-level.index'))->assertRedirect(route('login'));
        $this->sebagai($u)->get(route('admin.laporan-level.index'))->assertRedirect(route('admin.login'));
        $this->sebagai($u)->put(route('admin.laporan-level.update'), ['matriks' => []])->assertRedirect(route('admin.login'));
    }
}
