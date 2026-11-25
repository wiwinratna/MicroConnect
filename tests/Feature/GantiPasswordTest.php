<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * FR-13: ganti kata sandi.
 */
class GantiPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function pengguna(string $grup = 'pelakuusaha'): User
    {
        return User::create([
            'name' => 'Pengguna', 'email' => $grup . '@example.test',
            'password' => 'sandiLama123', 'user_group' => $grup,
            'must_change_password' => true,
        ]);
    }

    private function ganti(User $u, array $data)
    {
        return $this->actingAs($u)->post(route('password.change.update'), $data);
    }

    public function test_wrong_old_password_is_rejected(): void
    {
        $u = $this->pengguna();

        $this->ganti($u, ['current_password' => 'salah-banget', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertSessionHasErrors(['current_password' => 'Password lama salah.']);

        $u->refresh();
        $this->assertTrue(Hash::check('sandiLama123', $u->password));
        $this->assertTrue((bool) $u->must_change_password);
    }

    public function test_confirmation_mismatch_is_rejected(): void
    {
        $u = $this->pengguna();

        $this->ganti($u, ['current_password' => 'sandiLama123', 'password' => 'sandiBaru123', 'password_confirmation' => 'beda-jauh'])
            ->assertSessionHasErrors(['password' => 'Konfirmasi password tidak cocok.']);

        $this->assertTrue((bool) $u->fresh()->must_change_password);
    }

    public function test_new_password_must_differ_from_old(): void
    {
        $u = $this->pengguna();

        $this->ganti($u, ['current_password' => 'sandiLama123', 'password' => 'sandiLama123', 'password_confirmation' => 'sandiLama123'])
            ->assertSessionHasErrors('password');
    }

    public function test_old_password_is_required(): void
    {
        $this->ganti($this->pengguna(), ['password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertSessionHasErrors('current_password');
    }

    public function test_success_hashes_password_and_clears_flag(): void
    {
        $u = $this->pengguna();

        $this->ganti($u, ['current_password' => 'sandiLama123', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertRedirect(route('umkm.dashboard'));

        $u->refresh();
        $this->assertFalse((bool) $u->must_change_password);
        $this->assertNotSame('sandiBaru123', $u->password);
        $this->assertTrue(Hash::check('sandiBaru123', $u->password));
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = $this->pengguna('admin');

        $this->ganti($admin, ['current_password' => 'sandiLama123', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->post(route('password.change.update'), [
            'current_password' => 'x', 'password' => 'sandiBaru123', 'password_confirmation' => 'sandiBaru123',
        ])->assertRedirect();

        $this->assertGuest();
    }
}
