<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\PengajuanPendaftaran;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * FR-11 (BR-08, BR-13): persetujuan membuat akun dalam satu transaksi.
 */
class AktivasiAkunTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    public function test_approval_creates_user_and_umkm_from_pengajuan(): void
    {
        $p = $this->ajukan([
            'jenis_usaha_id' => $this->jenisId('Jasa boga'), 'mengolah_bahan' => '1',
        ]);

        $this->setujui($p, $this->level('L1'))->assertSessionHas('success');

        $user = User::where('email', 'budi.baru@example.test')->firstOrFail();
        $this->assertSame('pelakuusaha', $user->user_group);
        $this->assertNull($user->username);
        $this->assertSame('6281234567890', $user->no_wa);
        $this->assertFalse((bool) $user->must_change_password); // bawaan: ganti kata sandi tidak wajib

        $umkm = Umkm::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('aktif', $umkm->status);
        $this->assertSame($this->level('L1')->id, $umkm->level_id);
        $this->assertSame('barang', $umkm->rumpun);
        $this->assertTrue((bool) $umkm->memiliki_persediaan);
        $this->assertTrue((bool) $umkm->melakukan_produksi);
        $this->assertSame('Jasa boga', $umkm->jenis_usaha); // nama pendek, tanpa contoh
        $this->assertNotNull($umkm->tanggal_aktivasi);
        $this->assertTrue((bool) $umkm->persetujuan_data_pribadi);
        $this->assertGreaterThan(0, Coa::where('umkm_id', $umkm->id)->count());

        $p->refresh();
        $this->assertSame($umkm->id, $p->umkm_id);
        $this->assertSame($umkm->id, $p->penilaian->umkm_id);
    }

    public function test_initial_password_comes_from_config_and_is_hashed(): void
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));

        $user = User::where('email', $p->email)->firstOrFail();
        $this->assertNotSame(self::SANDI_AWAL, $user->password);
        $this->assertTrue(Hash::check(self::SANDI_AWAL, $user->password));
    }

    public function test_missing_initial_password_blocks_approval(): void
    {
        $p = $this->ajukan();
        config(['minect.initial_password' => null]);

        $this->setujui($p, $this->level('L1'))->assertSessionHas('error');

        $this->assertSame('menunggu', $p->fresh()->status);
        $this->assertSame(0, Umkm::count());
    }

    public function test_approving_twice_creates_only_one_account(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();

        $this->setujui($p, $this->level('L1'), null, $admin)->assertSessionHas('success');
        $this->setujui($p, $this->level('L1'), null, $admin)->assertSessionHas('error');

        $this->assertSame(1, Umkm::count());
        $this->assertSame(1, User::where('email', $p->email)->count());
    }

    public function test_reject_after_approval_is_refused(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();
        $this->setujui($p, $this->level('L1'), null, $admin);

        $this->actingAs($admin)
            ->post(route('admin.pengajuan.tolak', $p->id), ['alasan_penolakan' => 'Terlambat.'])
            ->assertSessionHas('error');

        $this->assertSame('disetujui', $p->fresh()->status);
    }

    public function test_failure_midway_leaves_no_partial_data(): void
    {
        $p = $this->ajukan();

        Umkm::creating(function () {
            throw new \RuntimeException('gagal di tengah');
        });

        try {
            $this->setujui($p, $this->level('L1'))->assertStatus(500);
        } finally {
            Umkm::flushEventListeners();
        }

        $this->assertSame(0, User::where('email', $p->email)->count());
        $this->assertSame(0, Umkm::count());
        $p->refresh();
        $this->assertSame('menunggu', $p->status);
        $this->assertNull($p->umkm_id);
        $this->assertNull($p->penilaian->level_akhir_id);
    }

    public function test_conflicting_account_blocks_approval_without_crash(): void
    {
        $p = $this->ajukan();

        // Akun lain dengan email yang sama dibuat setelah pengajuan masuk.
        User::create([
            'name' => 'Penyerobot', 'email' => $p->email,
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ]);

        $this->setujui($p, $this->level('L1'))->assertSessionHas('error');

        $this->assertSame('menunggu', $p->fresh()->status);
        $this->assertSame(0, Umkm::count());
    }

    public function test_by_default_new_account_can_use_system_without_changing_password(): void
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));
        auth()->logout();

        $this->post('/umkm/login', ['email' => $p->email, 'password' => self::SANDI_AWAL])
            ->assertRedirect(route('umkm.dashboard'));

        $this->get(route('umkm.penilaian.index'))->assertOk();
    }

    public function test_when_forced_new_account_must_change_password_first(): void
    {
        config(['minect.force_change_initial_password' => true]);
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));

        $this->assertTrue((bool) User::where('email', $p->email)->value('must_change_password'));
    }

    public function test_first_login_goes_to_change_password_and_locks_other_pages(): void
    {
        config(['minect.force_change_initial_password' => true]); // aturan BR-08 dihidupkan
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));
        auth()->logout();

        $this->post('/umkm/login', ['email' => $p->email, 'password' => self::SANDI_AWAL])
            ->assertRedirect(route('password.change'));

        $this->get(route('umkm.dashboard'))->assertRedirect(route('password.change'));
        $this->get(route('umkm.penilaian.index'))->assertRedirect(route('password.change'));
    }

    public function test_pilih_level_routes_are_closed(): void
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));
        $user = User::where('email', $p->email)->firstOrFail();
        $user->update(['must_change_password' => false]);

        $this->actingAs($user)->get('/umkm/pilih-level')->assertNotFound();
        $this->actingAs($user)->post('/umkm/pilih-level', ['level_id' => $this->level('L3')->id])->assertStatus(404);
        $this->assertSame($this->level('L1')->id, Umkm::where('user_id', $user->id)->value('level_id'));
    }

    public function test_applicant_has_no_account_before_approval(): void
    {
        $this->ajukan();

        $this->assertSame(0, User::where('user_group', 'pelakuusaha')->count());
        $this->assertSame(1, PengajuanPendaftaran::where('status', 'menunggu')->count());
    }
}
