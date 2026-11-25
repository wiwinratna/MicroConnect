<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\PeriodePenilaian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanPenilaianUlang;
use Tests\TestCase;

/**
 * FR-21: Admin membuka periode penilaian ulang; hanya UMKM aktif yang dinotifikasi.
 */
class PeriodeAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanPenilaianUlang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK4b();
    }

    private function buka(array $ubah = [])
    {
        return $this->sebagai($this->buatAdminK3())->post(route('admin.periode.store'), array_merge([
            'nama'            => 'Penilaian Ulang 2026',
            'tanggal_mulai'   => '2026-12-01',
            'tanggal_selesai' => '2026-12-15',
        ], $ubah));
    }

    public function test_opening_a_period_creates_it_and_notifies_only_active_umkm(): void
    {
        $a = $this->umkmAktif();
        $b = $this->umkmAktif(['no_whatsapp' => '081211112222']);
        $nonaktif = $this->umkmAktif(['status' => 'nonaktif']);

        $this->buka()->assertRedirect(route('admin.periode.index'))->assertSessionHas('success');

        $periode = PeriodePenilaian::firstOrFail();
        $this->assertSame('dibuka', $periode->status);
        $this->assertSame('2026-12-01', $periode->tanggal_mulai->toDateString());

        $this->assertSame(2, Notifikasi::where('jenis', 'periode_dibuka')->where('saluran', 'whatsapp')->count());
        $this->assertSame(2, Notifikasi::where('jenis', 'periode_dibuka')->where('saluran', 'aplikasi')->count());
        $this->assertSame(0, Notifikasi::where('umkm_id', $nonaktif->umkm->id)->count());
        $this->assertSame(2, Notifikasi::where('jenis', 'periode_dibuka')->whereIn('umkm_id', [$a->umkm->id, $b->umkm->id])->where('saluran', 'aplikasi')->count());
    }

    public function test_notification_uses_template_with_formatted_dates(): void
    {
        $this->umkmAktif();

        $this->buka();

        $wa = Notifikasi::where('jenis', 'periode_dibuka')->where('saluran', 'whatsapp')->first();
        $this->assertSame(
            'Periode penilaian ulang MINECT dibuka 01/12/2026–15/12/2026. Silakan isi penilaian melalui menu Penilaian.',
            $wa->isi
        );
        $this->assertSame('terkirim', $wa->status); // driver simulasi
        $this->assertSame('6281211110000', $wa->no_tujuan);
    }

    public function test_umkm_without_number_gets_app_copy_only(): void
    {
        $this->buatUmkm(['no_whatsapp' => null]);

        $this->buka();

        $this->assertSame(0, Notifikasi::where('jenis', 'periode_dibuka')->where('saluran', 'whatsapp')->count());
        $this->assertSame(1, Notifikasi::where('jenis', 'periode_dibuka')->where('saluran', 'aplikasi')->count());
    }

    public function test_umkm_sees_the_notification_in_its_inbox(): void
    {
        $u = $this->umkmAktif();
        $this->buka();

        $this->ke($u, '/umkm/notifikasi')->assertOk()
            ->assertSee('Periode penilaian ulang MINECT dibuka 01/12/2026')
            ->assertSee('Periode penilaian dibuka');
    }

    public function test_dates_are_validated(): void
    {
        $this->buka(['nama' => ''])->assertSessionHasErrors('nama');
        $this->buka(['tanggal_mulai' => ''])->assertSessionHasErrors('tanggal_mulai');
        $this->buka(['tanggal_selesai' => '2026-11-30'])->assertSessionHasErrors('tanggal_selesai'); // sebelum mulai
        $this->buka(['tanggal_mulai' => 'bukan-tanggal'])->assertSessionHasErrors('tanggal_mulai');

        $this->assertSame(0, PeriodePenilaian::count());
    }

    public function test_only_one_period_can_be_open_at_a_time(): void
    {
        $this->umkmAktif();
        $this->buka()->assertSessionHas('success');
        $sebelum = Notifikasi::count();

        $this->buka(['nama' => 'Periode Kedua'])->assertSessionHas('error');

        $this->assertSame(1, PeriodePenilaian::count());
        $this->assertSame($sebelum, Notifikasi::count()); // tidak ada notifikasi ganda
    }

    public function test_closing_allows_opening_a_new_period(): void
    {
        $admin = $this->buatAdminK3();
        $this->buka();
        $id = PeriodePenilaian::value('id');

        $this->sebagai($admin)->post(route('admin.periode.tutup', $id))->assertRedirect(route('admin.periode.index'));
        $this->assertSame('ditutup', PeriodePenilaian::find($id)->status);

        $this->buka(['nama' => 'Periode Kedua'])->assertSessionHas('success');
        $this->assertSame(2, PeriodePenilaian::count());
    }

    public function test_closing_twice_is_refused(): void
    {
        $periode = $this->buatPeriode(['status' => 'ditutup']);

        $this->sebagai($this->buatAdminK3())->post(route('admin.periode.tutup', $periode->id))->assertSessionHas('error');
    }

    public function test_period_is_in_effect_only_when_open_and_within_dates(): void
    {
        $this->assertTrue($this->buatPeriode()->berlaku());
        $this->assertFalse($this->buatPeriode(['status' => 'ditutup'])->berlaku());
        $this->assertFalse($this->buatPeriode(['tanggal_mulai' => now()->addDays(3)->toDateString(), 'tanggal_selesai' => now()->addDays(9)->toDateString()])->berlaku());
        $this->assertFalse($this->buatPeriode(['tanggal_mulai' => now()->subDays(9)->toDateString(), 'tanggal_selesai' => now()->subDay()->toDateString()])->berlaku());
        $this->assertTrue($this->buatPeriode(['tanggal_mulai' => now()->toDateString(), 'tanggal_selesai' => now()->toDateString()])->berlaku()); // hari yang sama
    }

    public function test_actions_are_written_to_audit_log(): void
    {
        $this->umkmAktif();
        $admin = $this->buatAdminK3();
        $this->buka();
        $id = PeriodePenilaian::value('id');
        $this->sebagai($admin)->post(route('admin.periode.tutup', $id));

        $this->assertSame(1, DB::table('log_audit')->where('aksi', 'periode.buka')->count());
        $this->assertSame(1, DB::table('log_audit')->where('aksi', 'periode.tutup')->count());
        $this->assertStringContainsString('"umkm_dinotifikasi":1', DB::table('log_audit')->where('aksi', 'periode.buka')->value('data_baru'));
    }

    public function test_screen_lists_periods_with_counts(): void
    {
        $this->buatPeriode(['nama' => 'Periode Terdaftar']);

        $this->sebagai($this->buatAdminK3())->get(route('admin.periode.index'))
            ->assertOk()->assertSee('Periode Terdaftar')->assertSee('Dibuka')->assertSee('Buka periode baru');
    }

    public function test_only_admin_can_manage_periods(): void
    {
        $u = $this->umkmAktif();
        $periode = $this->buatPeriode();

        $this->get(route('admin.periode.index'))->assertRedirect(route('login'));
        $this->sebagai($u)->get(route('admin.periode.index'))->assertRedirect(route('admin.login'));
        $this->sebagai($u)->post(route('admin.periode.store'), ['nama' => 'X', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02'])
            ->assertRedirect(route('admin.login'));
        $this->sebagai($u)->post(route('admin.periode.tutup', $periode->id))->assertRedirect(route('admin.login'));

        $this->assertSame('dibuka', $periode->fresh()->status);
        $this->assertSame(1, PeriodePenilaian::count());
    }
}
