<?php

namespace Tests\Feature;

use App\Models\PengajuanPendaftaran;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * FR-08, FR-09, FR-10: Admin meninjau dan memutuskan pengajuan.
 */
class PengajuanAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    // ---------- FR-08 ----------

    public function test_list_shows_only_waiting_pengajuan(): void
    {
        $this->ajukan(['nama_pemilik' => 'Pemilik Menunggu', 'email' => 'a@example.test', 'username' => 'pemilik_a', 'no_wa' => '081200000001']);
        $lain = $this->ajukan(['nama_pemilik' => 'Pemilik Ditolak', 'email' => 'b@example.test', 'username' => 'pemilik_b', 'no_wa' => '081200000002']);
        $lain->update(['status' => 'ditolak']);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.pengajuan.index'))
            ->assertOk()
            ->assertSee('Pemilik Menunggu')
            ->assertDontSee('Pemilik Ditolak');
    }

    public function test_list_marks_pengajuan_that_needs_admin_decision(): void
    {
        $this->ajukan([], 'daaa');

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.pengajuan.index'))
            ->assertSee('Perlu keputusan Admin');
    }

    // ---------- FR-09 ----------

    public function test_detail_shows_data_answers_scores_total_and_recommendation(): void
    {
        $p = $this->ajukan(['nama_usaha' => 'Toko Maju Jaya'], 'cbab');

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.pengajuan.show', $p->id))
            ->assertOk()
            ->assertSee('Toko Maju Jaya')
            ->assertSee('budi.baru@example.test')
            ->assertSee('Lebih dari Rp1,2 miliar sampai dengan Rp2 miliar') // jawaban P1 (skor 3)
            ->assertSee('Total skor')
            ->assertSee('Level 2 - Berkembang'); // 3+2+1+2 = 8
    }

    // ---------- FR-10 ----------

    public function test_approve_with_recommended_level_stores_decision(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();

        $this->setujui($p, $this->level('L1'), null, $admin)
            ->assertRedirect(route('admin.pengajuan.index'))
            ->assertSessionHas('success');

        $p->refresh();
        $nilai = $p->penilaian;
        $this->assertSame('disetujui', $p->status);
        $this->assertSame($admin->id, $p->diverifikasi_oleh);
        $this->assertNotNull($p->tanggal_verifikasi);
        $this->assertSame($this->level('L1')->id, $nilai->level_akhir_id);
        $this->assertNull($nilai->alasan_penetapan);
    }

    public function test_approve_with_different_level_requires_reason(): void
    {
        $p = $this->ajukan(); // rekomendasi L1

        $this->setujui($p, $this->level('L2'), null)->assertSessionHas('error');
        $this->setujui($p, $this->level('L2'), '   ')->assertSessionHas('error');

        $this->assertSame('menunggu', $p->fresh()->status);
        $this->assertSame(0, User::where('email', $p->email)->count());
    }

    public function test_approve_with_different_level_and_reason_stores_both(): void
    {
        $p = $this->ajukan();

        $this->setujui($p, $this->level('L2'), 'Usaha sudah berjalan lama dan rapi.')
            ->assertSessionHas('success');

        $nilai = $p->fresh()->penilaian;
        $this->assertSame($this->level('L2')->id, $nilai->level_akhir_id);
        $this->assertSame('Usaha sudah berjalan lama dan rapi.', $nilai->alasan_penetapan);
        $this->assertSame($this->level('L1')->id, $nilai->level_rekomendasi_id); // rekomendasi tidak berubah
        $this->assertSame($this->level('L2')->id, Umkm::first()->level_id);
    }

    public function test_reject_requires_reason(): void
    {
        $p = $this->ajukan();

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.pengajuan.tolak', $p->id), ['alasan_penolakan' => ''])
            ->assertSessionHasErrors('alasan_penolakan');

        $this->assertSame('menunggu', $p->fresh()->status);
    }

    public function test_reject_stores_reason_creates_no_account_and_allows_reapply(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->post(route('admin.pengajuan.tolak', $p->id), ['alasan_penolakan' => 'Data usaha tidak jelas.'])
            ->assertRedirect(route('admin.pengajuan.index'));

        $p->refresh();
        $this->assertSame('ditolak', $p->status);
        $this->assertSame('Data usaha tidak jelas.', $p->alasan_penolakan);
        $this->assertSame($admin->id, $p->diverifikasi_oleh);
        $this->assertSame(0, Umkm::count());
        $this->assertSame(1, User::count()); // hanya Admin

        // Yang ditolak boleh mengajukan lagi dengan data yang sama.
        auth()->logout();
        $this->post('/register', $this->payload())->assertRedirect(route('register.selesai'));
        $this->assertSame(2, PengajuanPendaftaran::count());
    }

    public function test_decision_is_written_to_audit_log(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();

        $this->setujui($p, $this->level('L2'), 'Alasan uji.', $admin);

        $log = DB::table('log_audit')->where('entitas_id', $p->id)->first();
        $this->assertSame('pengajuan.setujui', $log->aksi);
        $this->assertSame($admin->id, (int) $log->pengguna_id);
        $this->assertStringContainsString('L2', $log->data_baru);
        $this->assertStringContainsString('Alasan uji.', $log->data_baru);
    }

    // ---------- Otorisasi lewat URL langsung ----------

    public function test_guest_cannot_use_admin_pengajuan_routes(): void
    {
        $p = $this->ajukan();

        // Tamu dialihkan ke halaman masuk oleh middleware auth (perilaku v1.0).
        $this->get(route('admin.pengajuan.index'))->assertRedirect(route('login'));
        $this->get(route('admin.pengajuan.show', $p->id))->assertRedirect(route('login'));
        $this->post(route('admin.pengajuan.setujui', $p->id), ['level_akhir_id' => $this->level('L1')->id])
            ->assertRedirect(route('login'));
        $this->post(route('admin.pengajuan.tolak', $p->id), ['alasan_penolakan' => 'x'])
            ->assertRedirect(route('login'));

        $this->assertSame('menunggu', $p->fresh()->status);
        $this->assertSame(0, Umkm::count());
    }

    public function test_umkm_user_cannot_use_admin_pengajuan_routes(): void
    {
        $p = $this->ajukan();
        $umkm = User::create([
            'name' => 'Pelaku', 'email' => 'pelaku@example.test',
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ]);

        $this->actingAs($umkm)->get(route('admin.pengajuan.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($umkm)
            ->post(route('admin.pengajuan.setujui', $p->id), ['level_akhir_id' => $this->level('L1')->id])
            ->assertRedirect(route('admin.login'));

        $this->assertSame('menunggu', $p->fresh()->status);
        $this->assertSame(0, Umkm::count());
    }
}
