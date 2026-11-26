<?php

namespace Tests\Feature;

use App\Models\JenisUsaha;
use Database\Seeders\JenisUsahaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * FR-16: Admin mengelola kategori usaha dan jenisnya (barang/jasa).
 */
class KategoriUsahaAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    private function admin()
    {
        return $this->sebagai($this->buatAdminK3());
    }

    private function kategori(string $awalan): JenisUsaha
    {
        return JenisUsaha::where('nama', 'like', $awalan . '%')->firstOrFail();
    }

    public function test_page_lists_categories(): void
    {
        $this->admin()->get(route('admin.kategori-usaha.index'))
            ->assertOk()
            ->assertSee('Tambah kategori')
            ->assertSee('Perdagangan barang');
    }

    public function test_new_category_appears_in_registration_form(): void
    {
        $this->admin()->post(route('admin.kategori-usaha.store'), ['nama' => 'Manufaktur', 'rumpun_bawaan' => 'barang'])
            ->assertSessionHas('success');

        $this->assertTrue($this->kategori('Manufaktur')->aktif);

        auth()->logout();
        $this->get(route('register'))->assertOk()->assertSee('Manufaktur');
    }

    public function test_empty_duplicate_and_unknown_type_are_rejected(): void
    {
        $jumlah = JenisUsaha::count();

        $this->admin()->post(route('admin.kategori-usaha.store'), ['nama' => '', 'rumpun_bawaan' => 'barang'])
            ->assertSessionHasErrors('nama');
        $this->admin()->post(route('admin.kategori-usaha.store'), ['nama' => 'Pertanian', 'rumpun_bawaan' => 'barang'])
            ->assertSessionHasErrors('nama');
        $this->admin()->post(route('admin.kategori-usaha.store'), ['nama' => 'Baru', 'rumpun_bawaan' => 'lainnya'])
            ->assertSessionHasErrors('rumpun_bawaan');

        $this->assertSame($jumlah, JenisUsaha::count());
    }

    public function test_rename_updates_copied_name_on_umkm_but_keeps_custom_text(): void
    {
        $k = $this->kategori('Pertanian');
        $salinan = $this->buatUmkm(['jenis_usaha_id' => $k->id, 'jenis_usaha' => 'Pertanian', 'rumpun' => 'barang']);

        $this->admin()->put(route('admin.kategori-usaha.update', $k->id), ['nama' => 'Tani', 'rumpun_bawaan' => 'barang'])
            ->assertSessionHas('success');

        $this->assertSame('Tani', $salinan->umkm->fresh()->jenis_usaha);

        $salinan->umkm->update(['jenis_usaha' => 'Sayur Organik']);
        $this->admin()->put(route('admin.kategori-usaha.update', $k->id), ['nama' => 'Kebun', 'rumpun_bawaan' => 'barang']);

        $this->assertSame('Sayur Organik', $salinan->umkm->fresh()->jenis_usaha);
    }

    public function test_type_cannot_change_while_used_by_other_type(): void
    {
        $k = $this->kategori('Pertanian');
        $this->buatUmkm(['jenis_usaha_id' => $k->id, 'rumpun' => 'barang']);

        $this->admin()->put(route('admin.kategori-usaha.update', $k->id), ['nama' => $k->nama, 'rumpun_bawaan' => 'jasa'])
            ->assertSessionHasErrors('rumpun_bawaan');
        $this->assertSame('barang', $k->fresh()->rumpun_bawaan);

        $this->admin()->put(route('admin.kategori-usaha.update', $k->id), [
            'nama' => $k->nama, 'rumpun_bawaan' => 'ditentukan_umkm',
        ])->assertSessionHas('success');
    }

    public function test_deactivated_category_is_hidden_but_data_stays(): void
    {
        $k = $this->kategori('Peternakan');
        $u = $this->buatUmkm(['jenis_usaha_id' => $k->id, 'rumpun' => 'barang']);

        $this->admin()->patch(route('admin.kategori-usaha.status', $k->id))->assertSessionHas('success');

        $this->assertFalse($k->fresh()->aktif);
        $this->assertSame($k->id, $u->umkm->fresh()->jenis_usaha_id);

        auth()->logout();
        $this->get(route('register'))->assertDontSee('Peternakan');
    }

    public function test_reseeding_keeps_admin_changes(): void
    {
        $this->kategori('Peternakan')->update(['aktif' => false]);

        $this->seed(JenisUsahaSeeder::class);

        $this->assertFalse($this->kategori('Peternakan')->aktif);
    }

    public function test_only_admin_can_manage_categories(): void
    {
        $u = $this->buatUmkm();
        $jumlah = JenisUsaha::count();

        $this->sebagai($u)->post(route('admin.kategori-usaha.store'), ['nama' => 'Manufaktur', 'rumpun_bawaan' => 'barang']);

        $this->assertSame($jumlah, JenisUsaha::count());
    }
}
