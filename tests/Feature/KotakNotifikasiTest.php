<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\User;
use App\Services\Notifikasi\NotifikasiDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * Kotak Notifikasi: Admin melihat semua, UMKM hanya salinan aplikasi miliknya.
 */
class KotakNotifikasiTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    private function umkmDisetujui(string $namaUsaha, string $kode): User
    {
        $p = $this->ajukan([
            'email' => "{$kode}@example.test", 'username' => "umkm_{$kode}",
            'no_wa' => '08120000' . str_pad((string) crc32($kode) % 10000, 4, '0', STR_PAD_LEFT),
            'nama_usaha' => $namaUsaha,
        ]);
        $this->setujui($p, $this->level('L1'));
        auth()->logout();

        $user = User::where('email', $p->email)->firstOrFail();
        $user->update(['must_change_password' => false]);

        return $user;
    }

    public function test_admin_sees_all_notifications_including_applicants(): void
    {
        $this->ajukan(); // calon belum punya akun
        $p = $this->ajukan(['email' => 'dua@example.test', 'username' => 'pemilik_dua', 'no_wa' => '081200000002', 'nama_usaha' => 'Toko Dua']);
        $this->setujui($p, $this->level('L1'));

        $this->actingAs($this->buatAdmin())->get(route('admin.notifikasi.index'))
            ->assertOk()
            ->assertSee('Registrasi diterima')
            ->assertSee('Akun aktif')
            ->assertSee('Warung Budi')
            ->assertSee('Toko Dua')
            ->assertSee('Kata sandi awal'); // pesan WhatsApp simulasi terlihat untuk demo
    }

    public function test_admin_can_filter_by_type_channel_and_status(): void
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->get(route('admin.notifikasi.index', ['jenis' => 'akun_aktif']))
            ->assertSee('Akun aktif')->assertDontSee('Halo Budi Santoso');

        $this->actingAs($admin)->get(route('admin.notifikasi.index', ['saluran' => 'aplikasi']))
            ->assertSee('Anda dapat mengganti kata sandi awal')->assertDontSee('Kata sandi awal:');

        $this->actingAs($admin)->get(route('admin.notifikasi.index', ['status' => 'gagal']))
            ->assertSee('Belum ada notifikasi');
    }

    public function test_admin_page_shows_failure_reason(): void
    {
        $this->app->bind(NotifikasiDriver::class, fn () => new class implements NotifikasiDriver {
            public function kirim(Notifikasi $n): void
            {
                throw new \RuntimeException('gateway mati');
            }
        });
        $this->ajukan();

        $this->actingAs($this->buatAdmin())->get(route('admin.notifikasi.index', ['status' => 'gagal']))
            ->assertOk()->assertSee('gateway mati')->assertSee('Gagal');
    }

    public function test_umkm_sees_only_own_app_copies_without_password(): void
    {
        $a = $this->umkmDisetujui('Toko Alfa', 'alfa');
        $b = $this->umkmDisetujui('Toko Beta', 'beta');

        $halamanA = $this->actingAs(User::findOrFail($a->id))->get(route('umkm.notifikasi.index'))->assertOk();
        $halamanA->assertSee('Akun MINECT Toko Alfa telah aktif')
            ->assertDontSee('Toko Beta')
            ->assertDontSee(self::SANDI_AWAL)
            ->assertDontSee('Halo '); // notifikasi calon (registrasi) tidak masuk kotaknya

        $this->actingAs(User::findOrFail($b->id))->get(route('umkm.notifikasi.index'))
            ->assertSee('Akun MINECT Toko Beta telah aktif')->assertDontSee('Toko Alfa');
    }

    public function test_umkm_inbox_has_only_app_channel_rows(): void
    {
        $a = $this->umkmDisetujui('Toko Alfa', 'alfa');

        $this->assertSame(0, Notifikasi::where('umkm_id', $a->umkm->id)->where('saluran', 'aplikasi')->where('status', '!=', 'terkirim')->count());

        $this->actingAs(User::findOrFail($a->id))->get(route('umkm.notifikasi.index'))
            ->assertOk()->assertDontSee('6281');
    }

    public function test_empty_inbox_message(): void
    {
        $u = $this->buatUmkmTanpaNotifikasi();

        $this->actingAs($u)->get(route('umkm.notifikasi.index'))->assertOk()->assertSee('Belum ada notifikasi');
    }

    public function test_access_control(): void
    {
        $u = $this->umkmDisetujui('Toko Alfa', 'alfa');

        $this->get(route('admin.notifikasi.index'))->assertRedirect(route('login'));
        $this->get(route('umkm.notifikasi.index'))->assertRedirect(route('login'));

        $this->actingAs(User::findOrFail($u->id))->get(route('admin.notifikasi.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($this->buatAdmin())->get(route('umkm.notifikasi.index'))->assertForbidden();
    }

    private function buatUmkmTanpaNotifikasi(): User
    {
        $user = User::create([
            'name' => 'Lama', 'email' => 'lama@example.test',
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ]);
        \App\Models\Umkm::create(['kode_umkm' => 'UM00098', 'user_id' => $user->id]);

        return $user;
    }
}
