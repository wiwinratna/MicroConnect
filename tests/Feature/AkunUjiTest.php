<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Database\Seeders\AkunUji\AkunUjiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/** BR-07: masuk dengan email, dan akun khusus uji coba. */
class AkunUjiTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    private const SANDI = 'rahasia123';

    public function test_test_accounts_are_created_once_and_log_in_by_email(): void
    {
        $this->seedK1();
        config(['minect.initial_password' => self::SANDI]);

        $this->seed(AkunUjiSeeder::class);
        $this->seed(AkunUjiSeeder::class);

        $uji = Umkm::with('level', 'user')->where('nama_usaha', 'like', 'Uji %')->get();
        $this->assertSame(
            ['barang1' => 'L1', 'barang2' => 'L2', 'barang3' => 'L3', 'dagang' => 'L3', 'jasa' => 'L3', 'jasanonstok' => 'L3'],
            $uji->mapWithKeys(fn ($u) => [strstr($u->user->email, '@', true) => $u->level->kode])->sortKeys()->all()
        );
        $this->assertSame(1, User::where('email', 'adminuji@contoh.test')->where('user_group', 'admin')->count());

        $this->post(route('umkm.login.process'), ['email' => 'barang3@contoh.test', 'password' => self::SANDI])
            ->assertRedirect(route('umkm.dashboard'));
    }

    public function test_username_with_id_suffix_is_no_longer_accepted(): void
    {
        $this->post(route('umkm.login.process'), ['email' => 'barang3.id', 'password' => self::SANDI])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
