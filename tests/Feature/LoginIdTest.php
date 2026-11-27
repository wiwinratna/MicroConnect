<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Database\Seeders\AkunUji\AkunUjiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/** BR-07: masuk dengan ID login (username + ".id") atau email, dan akun khusus uji coba. */
class LoginIdTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    private const SANDI = 'rahasia123';

    public function test_umkm_can_log_in_with_id_or_email(): void
    {
        $this->buatAkun('minestore', 'pelakuusaha');

        foreach (['minestore.id', '  MineStore.ID ', 'minestore@contoh.test'] as $isian) {
            $this->post(route('umkm.login.process'), ['email' => $isian, 'password' => self::SANDI])
                ->assertRedirect(route('umkm.dashboard'));
            $this->assertAuthenticated();
            auth()->logout();
        }
    }

    public function test_admin_can_log_in_with_id(): void
    {
        $this->buatAkun('adminsatu', 'admin');

        $this->post(route('admin.login.process'), ['email' => 'adminsatu.id', 'password' => self::SANDI])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_username_without_suffix_or_wrong_password_is_rejected(): void
    {
        $this->buatAkun('minestore', 'pelakuusaha');

        foreach ([['minestore', self::SANDI], ['minestore.id', 'salah12345'], ['takada.id', self::SANDI]] as [$isian, $sandi]) {
            $this->post(route('umkm.login.process'), ['email' => $isian, 'password' => $sandi])
                ->assertSessionHasErrors(['email' => 'ID/email atau password salah.']);
            $this->assertGuest();
        }
    }

    public function test_inactive_account_cannot_log_in_with_id(): void
    {
        $this->buatAkun('minestore', 'pelakuusaha', 'nonaktif');

        $this->post(route('umkm.login.process'), ['email' => 'minestore.id', 'password' => self::SANDI])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_id_and_email_share_one_attempt_limit(): void
    {
        $this->buatAkun('minestore', 'pelakuusaha');

        foreach (['minestore.id', 'minestore@contoh.test', 'minestore.id', 'minestore@contoh.test', 'minestore.id'] as $isian) {
            $this->post(route('umkm.login.process'), ['email' => $isian, 'password' => 'salah12345']);
        }

        $this->post(route('umkm.login.process'), ['email' => 'minestore@contoh.test', 'password' => self::SANDI])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_test_accounts_are_created_once_with_each_level_and_type(): void
    {
        $this->seedK1();
        config(['minect.initial_password' => self::SANDI]);

        $this->seed(AkunUjiSeeder::class);
        $this->seed(AkunUjiSeeder::class);

        $uji = Umkm::with('level', 'user')->where('nama_usaha', 'like', 'Uji %')->get();
        $this->assertSame(
            ['barang1' => 'L1', 'barang2' => 'L2', 'barang3' => 'L3', 'dagang' => 'L3', 'jasa' => 'L3', 'jasanonstok' => 'L3'],
            $uji->mapWithKeys(fn ($u) => [$u->user->username => $u->level->kode])->sortKeys()->all()
        );
        $this->assertSame(1, User::where('username', 'adminuji')->where('user_group', 'admin')->count());

        $this->post(route('umkm.login.process'), ['email' => 'barang3.id', 'password' => self::SANDI])
            ->assertRedirect(route('umkm.dashboard'));
    }

    private function buatAkun(string $username, string $grup, string $status = 'aktif'): User
    {
        $user = User::create([
            'name' => $username, 'username' => $username, 'email' => "{$username}@contoh.test",
            'password' => self::SANDI, 'user_group' => $grup, 'must_change_password' => false,
        ]);

        if ($grup === 'pelakuusaha') {
            Umkm::create(['kode_umkm' => 'UM00001', 'user_id' => $user->id, 'status' => $status]);
        }

        return $user;
    }
}
