<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Panel penjualan pada dashboard UMKM dan halaman Edit COA. */
class PerbaikanDashboardCoaTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    public function test_service_without_stock_still_sees_recent_sales(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->ke($u, '/umkm/dashboard')
            ->assertOk()
            ->assertSee('Penjualan Terakhir')
            ->assertDontSee('Stok Bahan Menipis');
    }

    public function test_edit_page_opens_with_current_account(): void
    {
        $u = $this->buatUmkm([], 'L3');

        $this->ke($u, '/umkm/coa/' . $this->buatAkun($u)->id . '/edit')
            ->assertOk()
            ->assertSee('Edit Akun COA')
            ->assertSee('Kas Kecil')
            ->assertSee('value="119"', false);
    }

    public function test_update_saves_name_and_keeps_code(): void
    {
        $u = $this->buatUmkm([], 'L3');
        $akun = $this->buatAkun($u);

        $this->sebagai($u)->put("/umkm/coa/{$akun->id}", ['header_akun' => '1', 'nama_akun' => 'Kas Toko'])
            ->assertRedirect(route('umkm.coa.index'));

        $this->assertDatabaseHas('coa', ['id' => $akun->id, 'nama_akun' => 'Kas Toko', 'kode_akun' => '119']);
    }

    public function test_other_umkm_cannot_open_the_edit_page(): void
    {
        $akun = $this->buatAkun($this->buatUmkm([], 'L3'));

        $this->ke($this->buatUmkm([], 'L3'), "/umkm/coa/{$akun->id}/edit")->assertForbidden();
    }

    private function buatAkun(User $user): Coa
    {
        return Coa::create([
            'umkm_id' => $user->umkm->id, 'header_akun' => '1', 'kode_akun' => '119',
            'nama_akun' => 'Kas Kecil', 'posisi_dr_cr' => 'Debit',
        ]);
    }
}
