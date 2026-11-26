<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** BR-07: akun yang dinonaktifkan langsung kehilangan akses, walau sesinya masih ada. */
class AkunNonaktifTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
        $this->user = $this->buatUmkm([], 'L3');
    }

    private function nonaktifkan(): void
    {
        $this->user->umkm->update(['status' => 'nonaktif']);
    }

    public function test_active_session_ends_on_next_request(): void
    {
        $this->sebagai($this->user)->get('/umkm/dashboard')->assertOk();
        $this->nonaktifkan();

        $this->sebagai($this->user)->get('/umkm/dashboard')
            ->assertRedirect(route('umkm.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_write_requests_change_nothing(): void
    {
        $this->nonaktifkan();

        $this->sebagai($this->user)->put(route('umkm.profile.update'), [
            'name' => 'Diubah', 'email' => $this->user->email,
        ])->assertRedirect(route('umkm.login'));

        $this->assertNotSame('Diubah', $this->user->fresh()->name);
    }

    public function test_json_request_is_refused(): void
    {
        $this->nonaktifkan();

        $this->sebagai($this->user)->getJson('/umkm/dashboard')->assertForbidden();
        $this->assertGuest();
    }

    public function test_password_page_is_also_closed(): void
    {
        $this->nonaktifkan();

        $this->sebagai($this->user)->post(route('password.change.update'), [
            'current_password' => 'rahasia123', 'password' => 'sandibaru1', 'password_confirmation' => 'sandibaru1',
        ])->assertRedirect(route('umkm.login'));

        $this->assertTrue(Hash::check('rahasia123', $this->user->fresh()->password));
    }

    public function test_remember_me_token_is_replaced(): void
    {
        $this->user->forceFill(['remember_token' => 'token-lama'])->save();
        $this->nonaktifkan();

        $this->sebagai($this->user)->get('/umkm/dashboard');

        $this->assertNotSame('token-lama', $this->user->fresh()->remember_token);
    }

    public function test_reactivated_account_works_and_admin_is_unaffected(): void
    {
        $this->nonaktifkan();
        $this->user->umkm->update(['status' => 'aktif']);

        $this->sebagai($this->user)->get('/umkm/dashboard')->assertOk();
        $this->sebagai($this->buatAdminK3())->get(route('admin.dashboard'))->assertOk();
    }
}
