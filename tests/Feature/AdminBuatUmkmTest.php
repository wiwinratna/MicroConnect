<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\PenilaianLevel;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/** Admin menambah UMKM: jenis usaha, kategori, kuesioner, level, dan sandi awal. */
class AdminBuatUmkmTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedK1();
        Mail::fake();
        config(['minect.initial_password' => 'sandi-uji-123', 'minect.force_change_initial_password' => false]);
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin.buat@example.test', 'password' => 'rahasia123', 'user_group' => 'admin',
        ]);
    }

    private function data(array $ubah = [], string $huruf = 'aabc'): array
    {
        return $ubah + [
            'name' => 'Pemilik Baru', 'email' => 'baru@example.test', 'nama_usaha' => 'Bengkel Baru',
            'rumpun' => 'jasa', 'jenis_usaha_id' => $this->jenisId('Jasa perorangan'), 'punya_persediaan' => '1',
            'jawaban' => $this->jawab($huruf), 'level_id' => '', 'persetujuan_data_pribadi' => '1',
        ];
    }

    private function kirim(array $data)
    {
        return $this->actingAs($this->admin)->post(route('admin.umkm.store'), $data);
    }

    private function level(string $kode): int
    {
        return (int) DB::table('umkm_level')->where('kode', $kode)->value('id');
    }

    public function test_form_shows_business_type_and_questions(): void
    {
        $this->actingAs($this->admin)->get(route('admin.umkm.create'))->assertOk()
            ->assertSee('Jenis usaha')->assertSee('Kategori usaha')->assertSee('Kuesioner');
    }

    public function test_creates_umkm_with_profile_recommended_level_and_history(): void
    {
        $this->kirim($this->data())->assertRedirect(route('admin.umkm.index'))->assertSessionHasNoErrors();

        $user = User::where('email', 'baru@example.test')->firstOrFail();
        $umkm = Umkm::where('user_id', $user->id)->firstOrFail();

        $this->assertTrue(Hash::check('sandi-uji-123', $user->password));
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertSame('jasa', $umkm->rumpun);
        $this->assertSame('Jasa perorangan', $umkm->jenis_usaha);
        $this->assertTrue((bool) $umkm->memiliki_persediaan);
        $this->assertFalse((bool) $umkm->melakukan_produksi);
        $this->assertTrue((bool) $umkm->persetujuan_data_pribadi);
        $this->assertSame($this->level('L2'), $umkm->level_id);
        $this->assertGreaterThan(0, DB::table('coa')->where('umkm_id', $umkm->id)->count());

        $n = PenilaianLevel::with('detail')->where('umkm_id', $umkm->id)->firstOrFail();
        $this->assertSame(['awal', 7, $this->level('L2'), $this->level('L2')],
            [$n->jenis, $n->total_skor, $n->level_rekomendasi_id, $n->level_akhir_id]);
        $this->assertCount(4, $n->detail);

        $this->assertSame(1, DB::table('log_audit')->where('aksi', 'umkm.buat')->where('entitas_id', $umkm->id)->count());
        $this->assertTrue(Notifikasi::where('umkm_id', $umkm->id)->where('jenis', 'akun_aktif')->exists());
    }

    public function test_force_change_switch_is_respected(): void
    {
        config(['minect.force_change_initial_password' => true]);

        $this->kirim($this->data())->assertSessionHasNoErrors();

        $this->assertTrue((bool) User::where('email', 'baru@example.test')->value('must_change_password'));
    }

    public function test_different_level_needs_reason(): void
    {
        $this->kirim($this->data(['level_id' => $this->level('L3')]))->assertSessionHasErrors('alasan');
        $this->assertSame(0, Umkm::count());

        $this->kirim($this->data(['level_id' => $this->level('L3'), 'alasan' => 'Omzet nyata lebih besar']))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->level('L3'), Umkm::firstOrFail()->level_id);
        $this->assertSame('Omzet nyata lebih besar', PenilaianLevel::firstOrFail()->alasan_penetapan);
    }

    public function test_incomplete_or_mismatched_input_saves_nothing(): void
    {
        $this->kirim($this->data(['rumpun' => null]))->assertSessionHasErrors('rumpun');
        $this->kirim($this->data(['rumpun' => 'barang', 'mengolah_bahan' => '1']))->assertSessionHasErrors('jenis_usaha_id');
        $this->kirim($this->data(['punya_persediaan' => null]))->assertSessionHasErrors('punya_persediaan');
        $this->kirim($this->data(['jawaban' => []]))->assertSessionHasErrors();
        $this->kirim($this->data(['persetujuan_data_pribadi' => null]))->assertSessionHasErrors('persetujuan_data_pribadi');
        $this->kirim($this->data(['nama_usaha' => '']))->assertSessionHasErrors('nama_usaha');

        $this->assertSame(0, Umkm::count());
        $this->assertSame(1, User::count());
    }

    public function test_missing_initial_password_is_reported(): void
    {
        config(['minect.initial_password' => null]);

        $this->kirim($this->data())->assertSessionHasErrors('email');

        $this->assertSame(0, Umkm::count());
    }
}
