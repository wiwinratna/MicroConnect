<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * Keputusan pemilik: ganti kata sandi awal tidak wajib. Saat login pertama muncul pop up
 * yang menawarkan ganti, bisa dilewati, dan menunjukkan tempat mengganti nanti.
 */
class SaranGantiSandiTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    /** Akun baru hasil persetujuan, lalu keluar. */
    private function akunBaru(): User
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));
        auth()->logout();

        return User::where('email', $p->email)->firstOrFail();
    }

    private function masuk(User $u, string $sandi = self::SANDI_AWAL)
    {
        return $this->post('/umkm/login', ['email' => $u->email, 'password' => $sandi]);
    }

    public function test_first_login_goes_to_dashboard_and_offers_password_change(): void
    {
        $u = $this->akunBaru();

        $this->masuk($u)
            ->assertRedirect(route('umkm.dashboard'))
            ->assertSessionHas('saran_ganti_sandi', true);

        $this->get(route('umkm.dashboard'))
            ->assertOk()
            ->assertSee('Ganti kata sandi?')
            ->assertSee('Ganti sekarang')
            ->assertSee('Lewati')
            ->assertSee('Profil UMKM')      // tempat mengganti nanti
            ->assertSee('Ubah password');   // nama bagiannya di halaman Profil
    }

    public function test_offer_appears_only_once_per_login(): void
    {
        $u = $this->akunBaru();
        $this->masuk($u);

        $this->get(route('umkm.dashboard'))->assertSee('Ganti kata sandi?');
        $this->get(route('umkm.dashboard'))->assertDontSee('Ganti kata sandi?');
    }

    public function test_no_offer_when_password_was_already_changed(): void
    {
        $u = $this->akunBaru();
        User::whereKey($u->id)->update(['password' => Hash::make('sandiPilihanSendiri1')]);

        $this->masuk($u, 'sandiPilihanSendiri1')
            ->assertRedirect(route('umkm.dashboard'))
            ->assertSessionMissing('saran_ganti_sandi');

        $this->get(route('umkm.dashboard'))->assertDontSee('Ganti kata sandi?');
    }

    public function test_no_offer_and_no_error_when_initial_password_is_not_configured(): void
    {
        $u = $this->akunBaru();
        config(['minect.initial_password' => null]);

        $this->masuk($u)->assertRedirect(route('umkm.dashboard'))->assertSessionMissing('saran_ganti_sandi');
    }

    public function test_skipping_does_not_lock_anything(): void
    {
        $u = $this->akunBaru();
        $this->masuk($u);

        // Pengguna mengabaikan pop up dan langsung memakai fitur lain.
        $this->get(route('umkm.penilaian.index'))->assertOk();
        $this->get('/umkm/produk')->assertOk();
    }

    public function test_when_forced_user_goes_to_change_page_instead_of_popup(): void
    {
        config(['minect.force_change_initial_password' => true]);
        $u = $this->akunBaru();

        $this->masuk($u)
            ->assertRedirect(route('password.change'))
            ->assertSessionMissing('saran_ganti_sandi');
    }

    public function test_change_page_is_optional_when_not_forced(): void
    {
        $u = $this->akunBaru();

        $this->actingAs($u)->get(route('password.change'))
            ->assertOk()
            ->assertSee('Nanti saja')
            ->assertSee('tidak wajib')
            ->assertDontSee('wajib mengganti password');
    }

    public function test_change_page_stays_mandatory_when_forced(): void
    {
        $u = $this->akunBaru();
        User::whereKey($u->id)->update(['must_change_password' => true]);

        $this->actingAs(User::findOrFail($u->id))->get(route('password.change'))
            ->assertOk()
            ->assertSee('wajib mengganti password')
            ->assertDontSee('Nanti saja');
    }

    public function test_user_can_change_password_voluntarily(): void
    {
        $u = $this->akunBaru();

        $this->actingAs($u)->post(route('password.change.update'), [
            'current_password' => self::SANDI_AWAL,
            'password' => 'sandiBaruKu123', 'password_confirmation' => 'sandiBaruKu123',
        ])->assertRedirect(route('umkm.dashboard'));

        $this->assertTrue(Hash::check('sandiBaruKu123', $u->fresh()->password));
        $this->assertFalse((bool) $u->fresh()->must_change_password);
    }

    public function test_admin_login_is_not_affected(): void
    {
        $admin = $this->buatAdmin('admin.sandi@example.test');

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionMissing('saran_ganti_sandi');
    }
}
