<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/**
 * FR-07 / BR-07: login hanya untuk akun yang ada dan aktif.
 */
class LoginStatusTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    private function akun(string $status): User
    {
        $user = User::create([
            'name' => 'Pemilik', 'email' => 'pemilik@example.test',
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ]);

        Umkm::create(['kode_umkm' => 'UM00001', 'user_id' => $user->id, 'status' => $status]);

        return $user;
    }

    private function masuk(string $email = 'pemilik@example.test', string $sandi = 'rahasia123')
    {
        return $this->post('/umkm/login', ['email' => $email, 'password' => $sandi]);
    }

    public function test_active_account_can_login(): void
    {
        $this->akun('aktif');

        $this->masuk()->assertRedirect(route('umkm.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_inactive_account_is_rejected_with_message(): void
    {
        $this->akun('nonaktif');

        $this->masuk()->assertSessionHasErrors(['email' => 'Akun Anda sedang nonaktif. Hubungi Admin KADIN.']);
        $this->assertGuest();
    }

    public function test_wrong_password_keeps_generic_message(): void
    {
        $this->akun('aktif');

        $this->masuk('pemilik@example.test', 'salahsalah')
            ->assertSessionHasErrors(['email' => 'ID/email atau password salah.']);
        $this->assertGuest();
    }

    public function test_applicant_with_waiting_pengajuan_cannot_login_and_is_told_why(): void
    {
        $this->seedK1();
        $this->post('/register', $this->payload())->assertRedirect(route('register.selesai'));

        // email, username, dan nomor WA yang masih berupa pengajuan tidak punya akun
        $this->masuk('budi.baru@example.test', 'apa-saja')
            ->assertSessionHasErrors(['email' => 'Pendaftaran Anda masih menunggu verifikasi Admin.']);
        $this->assertGuest();

        $this->masuk('budi_baru', 'apa-saja')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unknown_email_gets_generic_message(): void
    {
        $this->masuk('tidak.ada@example.test', 'x12345')
            ->assertSessionHasErrors(['email' => 'ID/email atau password salah.']);
    }
}
