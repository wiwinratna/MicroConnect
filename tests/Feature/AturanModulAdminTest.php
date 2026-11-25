<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * FR-17: Admin mengatur aturan tampilan modul per rumpun (kondisi dan label).
 */
class AturanModulAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    private function modulId(string $kode): int
    {
        return (int) DB::table('modul')->where('kode', $kode)->value('id');
    }

    /** Kirim satu perubahan aturan (modul, rumpun) lewat layar Admin. */
    private function ubah(string $kode, string $rumpun, string $kondisi, ?string $label = null)
    {
        return $this->sebagai($this->buatAdminK3())->put(route('admin.aturan-modul.update'), [
            'aturan' => [$this->modulId($kode) => [$rumpun => ['kondisi' => $kondisi, 'label_tampil' => $label]]],
        ]);
    }

    public function test_screen_lists_all_modules_and_conditions(): void
    {
        $this->sebagai($this->buatAdminK3())->get(route('admin.aturan-modul.index'))
            ->assertOk()
            ->assertSee('Bahan Baku')->assertSee('Kartu Stok')->assertSee('Metode Penilaian Persediaan')
            ->assertSee('Produksi')->assertSee('Pembelian')->assertSee('Penjualan')->assertSee('Laporan')
            ->assertSee('Jika memiliki persediaan')->assertSee('Tidak ditampilkan');
    }

    public function test_changing_a_rule_immediately_changes_umkm_access(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => true, 'melakukan_produksi' => false]);
        $this->assertTersedia($u, '/umkm/bahan-baku');

        $this->ubah('bahan_baku', 'jasa', 'tidak_ditampilkan')->assertRedirect(route('admin.aturan-modul.index'));

        $this->assertDiblokir($u, '/umkm/bahan-baku');
    }

    public function test_changing_a_rule_for_one_rumpun_does_not_affect_the_other(): void
    {
        $barang = $this->buatUmkm(['rumpun' => 'barang', 'memiliki_persediaan' => true, 'melakukan_produksi' => true]);

        $this->ubah('bahan_baku', 'jasa', 'tidak_ditampilkan');

        $this->assertTersedia($barang, '/umkm/bahan-baku');
    }

    public function test_label_change_shows_in_umkm_menu(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->ubah('produk', 'jasa', 'selalu', 'Layanan Kami')->assertSessionHas('success');

        $this->ke($u, '/umkm/penilaian')->assertSee('Layanan Kami')->assertDontSee('Daftar Layanan Jasa');
    }

    public function test_condition_always_makes_a_hidden_module_visible_again(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);
        $this->assertDiblokir($u, '/umkm/bahan-baku');

        $this->ubah('bahan_baku', 'jasa', 'selalu');

        $this->assertTersedia($u, '/umkm/bahan-baku');
    }

    public function test_invalid_condition_is_rejected_and_nothing_changes(): void
    {
        $this->ubah('bahan_baku', 'jasa', 'sembarang')->assertSessionHasErrors();

        $this->assertSame(
            'jika_memiliki_persediaan',
            DB::table('aturan_modul')->where('modul_id', $this->modulId('bahan_baku'))->where('rumpun', 'jasa')->value('kondisi')
        );
    }

    public function test_only_one_rule_exists_per_module_and_rumpun(): void
    {
        $this->ubah('bahan_baku', 'jasa', 'selalu');
        $this->ubah('bahan_baku', 'jasa', 'tidak_ditampilkan');

        $this->assertSame(1, DB::table('aturan_modul')->where('modul_id', $this->modulId('bahan_baku'))->where('rumpun', 'jasa')->count());
        $this->assertSame(18, DB::table('aturan_modul')->count());

        // Aturan ganda ditolak oleh basis data.
        $this->expectException(QueryException::class);
        DB::table('aturan_modul')->insert([
            'modul_id' => $this->modulId('bahan_baku'), 'rumpun' => 'jasa', 'kondisi' => 'selalu',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_change_is_written_to_audit_log(): void
    {
        $this->ubah('produksi', 'barang', 'selalu');

        $log = DB::table('log_audit')->where('aksi', 'aturan_modul.ubah')->first();
        $this->assertNotNull($log);
        $this->assertSame($this->modulId('produksi'), (int) $log->entitas_id);
        $this->assertStringContainsString('jika_melakukan_produksi', $log->data_lama);
        $this->assertStringContainsString('selalu', $log->data_baru);
    }

    public function test_unchanged_save_writes_no_audit_log(): void
    {
        $this->ubah('produksi', 'barang', 'jika_melakukan_produksi');

        $this->assertSame(0, DB::table('log_audit')->where('aksi', 'aturan_modul.ubah')->count());
    }

    public function test_only_admin_can_open_or_change_rules(): void
    {
        $u = $this->buatUmkm();

        $this->get(route('admin.aturan-modul.index'))->assertRedirect(route('login'));
        $this->sebagai($u)->get(route('admin.aturan-modul.index'))->assertRedirect(route('admin.login'));
        $this->sebagai($u)->put(route('admin.aturan-modul.update'), [
            'aturan' => [$this->modulId('bahan_baku') => ['jasa' => ['kondisi' => 'selalu']]],
        ])->assertRedirect(route('admin.login'));

        $this->assertSame(
            'jika_memiliki_persediaan',
            DB::table('aturan_modul')->where('modul_id', $this->modulId('bahan_baku'))->where('rumpun', 'jasa')->value('kondisi')
        );
    }
}
