<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * FR-14 (BR-02, BR-03): UMKM melihat hasil penilaiannya sendiri.
 */
class HasilPenilaianTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    /** Ajukan, setujui, lalu kembalikan pengguna UMKM yang sudah ganti kata sandi. */
    private function umkmDisetujui(array $ubah, string $huruf, string $kodeLevel, ?string $alasan = null): User
    {
        $p = $this->ajukan($ubah, $huruf);
        $this->setujui($p, $this->level($kodeLevel), $alasan);
        auth()->logout();

        $user = User::where('email', $p->email)->firstOrFail();
        $user->update(['must_change_password' => false]);

        return $user;
    }

    public function test_page_shows_scores_total_recommendation_and_final_level(): void
    {
        $u = $this->umkmDisetujui([], 'abab', 'L1'); // 1+2+1+2 = 6

        $this->actingAs($u)->get(route('umkm.penilaian.index'))
            ->assertOk()
            ->assertSee('Penilaian awal')
            ->assertSee('Total skor')
            ->assertSee('Lebih dari Rp100 juta sampai dengan Rp500 juta') // jawaban P2 (b), salinan teks
            ->assertSee('Melakukan pencatatan setiap hari.')              // jawaban P4 (b)
            ->assertSee('Level rekomendasi sistem')
            ->assertSee('Level 1 - Dasar');
    }

    public function test_reason_is_hidden_when_final_level_equals_recommendation(): void
    {
        $u = $this->umkmDisetujui([], 'aaaa', 'L1', 'Alasan yang tidak perlu tampil.');

        $this->actingAs($u)->get(route('umkm.penilaian.index'))
            ->assertOk()
            ->assertDontSee('Alasan penetapan')
            ->assertDontSee('Alasan yang tidak perlu tampil.');
    }

    public function test_reason_is_shown_when_final_level_differs(): void
    {
        $u = $this->umkmDisetujui([], 'aaaa', 'L2', 'Usaha sudah rapi dan berjalan lama.');

        $this->actingAs($u)->get(route('umkm.penilaian.index'))
            ->assertOk()
            ->assertSee('Alasan penetapan')
            ->assertSee('Usaha sudah rapi dan berjalan lama.')
            ->assertSee('Level 2 - Berkembang');
    }

    public function test_umkm_sees_only_its_own_assessment(): void
    {
        $a = $this->umkmDisetujui(['email' => 'a@example.test', 'username' => 'pemilik_a', 'no_wa' => '081200000001'], 'aaaa', 'L1');
        $b = $this->umkmDisetujui(['email' => 'b@example.test', 'username' => 'pemilik_b', 'no_wa' => '081200000002'], 'cccc', 'L3');

        $halamanB = $this->actingAs($b)->get(route('umkm.penilaian.index'));
        $halamanB->assertSee('Level 3 - Lanjut')->assertDontSee('Level 1 - Dasar');
        $this->assertSame(1, substr_count($halamanB->getContent(), 'Penilaian awal'));

        $this->actingAs($a)->get(route('umkm.penilaian.index'))
            ->assertSee('Level 1 - Dasar')->assertDontSee('Level 3 - Lanjut');
    }

    public function test_legacy_umkm_without_assessment_sees_empty_message(): void
    {
        $user = User::create([
            'name' => 'Lama', 'email' => 'lama@example.test',
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ]);
        Umkm::create(['kode_umkm' => 'UM00099', 'user_id' => $user->id]);

        $this->actingAs($user)->get(route('umkm.penilaian.index'))
            ->assertOk()
            ->assertSee('Belum ada penilaian');
    }

    public function test_guest_and_admin_cannot_open_umkm_assessment_page(): void
    {
        $this->get(route('umkm.penilaian.index'))->assertRedirect(route('login'));
        $this->actingAs($this->buatAdmin())->get(route('umkm.penilaian.index'))->assertForbidden();
    }
}
