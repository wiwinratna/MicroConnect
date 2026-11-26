<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * FR-27: Admin melengkapi jenis usaha, kategori, persediaan, dan produksi UMKM lama.
 */
class ProfilUsahaAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    private function simpan(int $umkmId, array $data)
    {
        return $this->sebagai($this->buatAdminK3())->put(route('admin.umkm.updateProfilUsaha', $umkmId), $data);
    }

    public function test_detail_page_shows_profile_form_with_hint_for_legacy_umkm(): void
    {
        $u = $this->buatUmkm();

        $this->sebagai($this->buatAdminK3())->get(route('admin.umkm.show', $u->umkm->id))
            ->assertOk()
            ->assertSee('Profil Usaha')
            ->assertSee('Jenis usaha belum dilengkapi')
            ->assertSee('Jasa boga');
    }

    public function test_before_completion_nothing_is_hidden_and_after_completion_rules_apply(): void
    {
        $u = $this->buatUmkm([], 'L2');
        $this->assertTersedia($u, '/umkm/bahan-baku');

        $this->simpan($u->umkm->id, ['rumpun' => 'jasa', 'memiliki_persediaan' => '0'])
            ->assertSessionHas('success');

        $this->assertDiblokir($u, '/umkm/bahan-baku');
        $this->assertDiblokir($u, '/umkm/kartu-stok');
        $this->assertTersedia($u, '/umkm/produk');
    }

    public function test_goods_rumpun_sets_stock_true_and_stores_production_answer(): void
    {
        $u = $this->buatUmkm();

        $this->simpan($u->umkm->id, ['rumpun' => 'barang', 'melakukan_produksi' => '0'])->assertSessionHas('success');

        $umkm = $u->umkm->fresh();
        $this->assertSame('barang', $umkm->rumpun);
        $this->assertTrue((bool) $umkm->memiliki_persediaan);
        $this->assertFalse((bool) $umkm->melakukan_produksi);
        $this->assertDiblokir($u, '/umkm/bahan-baku'); // barang tanpa produksi
    }

    public function test_service_rumpun_forces_production_false(): void
    {
        $u = $this->buatUmkm();

        $this->simpan($u->umkm->id, ['rumpun' => 'jasa', 'memiliki_persediaan' => '1', 'melakukan_produksi' => '1']);

        $umkm = $u->umkm->fresh();
        $this->assertTrue((bool) $umkm->memiliki_persediaan);
        $this->assertFalse((bool) $umkm->melakukan_produksi);
        $this->assertTersedia($u, '/umkm/bahan-baku');
    }

    public function test_business_type_is_saved_with_short_legacy_name(): void
    {
        $u = $this->buatUmkm(['jenis_usaha' => 'Kuliner']);

        $this->simpan($u->umkm->id, [
            'jenis_usaha_id' => $this->jenisId('Jasa boga'), 'rumpun' => 'barang', 'melakukan_produksi' => '1',
        ]);

        $umkm = $u->umkm->fresh();
        $this->assertSame($this->jenisId('Jasa boga'), $umkm->jenis_usaha_id);
        $this->assertSame('Jasa boga', $umkm->jenis_usaha);
    }

    public function test_validation_requires_rumpun_and_matching_answer(): void
    {
        $u = $this->buatUmkm();
        $id = $u->umkm->id;

        $this->simpan($id, [])->assertSessionHasErrors('rumpun');
        $this->simpan($id, ['rumpun' => 'barang'])->assertSessionHasErrors('melakukan_produksi');
        $this->simpan($id, ['rumpun' => 'jasa'])->assertSessionHasErrors('memiliki_persediaan');
        $this->simpan($id, ['rumpun' => 'lainnya'])->assertSessionHasErrors('rumpun');
        $this->simpan($id, ['rumpun' => 'jasa', 'memiliki_persediaan' => '1', 'jenis_usaha_id' => 99999])->assertSessionHasErrors('jenis_usaha_id');

        $this->assertNull($u->umkm->fresh()->rumpun);
    }

    public function test_category_must_match_business_type(): void
    {
        $u = $this->buatUmkm();
        $id = $u->umkm->id;

        $this->simpan($id, [
            'rumpun' => 'barang', 'melakukan_produksi' => '0', 'jenis_usaha_id' => $this->jenisId('Jasa perorangan'),
        ])->assertSessionHasErrors('jenis_usaha_id');

        $u->umkm->update(['jenis_usaha_id' => $this->jenisId('Jasa perorangan')]);

        // Kategori lama tidak cocok dengan jenis baru dan tidak diganti.
        $this->simpan($id, ['rumpun' => 'barang', 'melakukan_produksi' => '0'])
            ->assertSessionHasErrors('jenis_usaha_id');

        $this->assertNull($u->umkm->fresh()->rumpun);
    }

    public function test_change_is_written_to_audit_log(): void
    {
        $u = $this->buatUmkm();

        $this->simpan($u->umkm->id, ['rumpun' => 'jasa', 'memiliki_persediaan' => '0']);

        $log = DB::table('log_audit')->where('aksi', 'umkm.profil_usaha')->first();
        $this->assertNotNull($log);
        $this->assertSame($u->umkm->id, (int) $log->entitas_id);
        $this->assertStringContainsString('"rumpun":null', $log->data_lama);
        $this->assertStringContainsString('"rumpun":"jasa"', $log->data_baru);
    }

    public function test_only_admin_can_change_profile(): void
    {
        $u = $this->buatUmkm();
        $data = ['rumpun' => 'jasa', 'memiliki_persediaan' => '0'];

        $this->put(route('admin.umkm.updateProfilUsaha', $u->umkm->id), $data)->assertRedirect(route('login'));
        $this->sebagai($u)->put(route('admin.umkm.updateProfilUsaha', $u->umkm->id), $data)->assertRedirect(route('admin.login'));

        $this->assertNull($u->umkm->fresh()->rumpun);
    }

    public function test_unknown_umkm_returns_404(): void
    {
        $this->simpan(999999, ['rumpun' => 'jasa', 'memiliki_persediaan' => '0'])->assertNotFound();
    }
}
