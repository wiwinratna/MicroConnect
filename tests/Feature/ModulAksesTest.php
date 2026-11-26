<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/**
 * FR-19: fitur operasional v1.0 ditampilkan dan dibatasi menurut aturan_modul (Tabel 3.8).
 * Pembatasan berlaku di server (URL langsung), bukan hanya menu.
 */
class ModulAksesTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    private const URL = [
        'bahan'     => '/umkm/bahan-baku',
        'produk'    => '/umkm/produk',
        'pembelian' => '/umkm/pembelian',
        'penjualan' => '/umkm/penjualan',
        'kasir'     => '/umkm/kasir',
        'beban'     => '/umkm/beban',
        'kartu'     => '/umkm/kartu-stok',
        'produksi'  => '/umkm/produksi',
        'profil'    => '/umkm/profile',
        'sidebar'   => '/umkm/penilaian', // halaman ringan untuk memeriksa menu
    ];

    // ---------- Jasa tanpa persediaan ----------

    public function test_service_without_stock_cannot_use_stock_modules_even_by_direct_url(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->assertDiblokir($u, self::URL['bahan']);
        $this->assertDiblokir($u, self::URL['kartu']);
        $this->assertDiblokir($u, self::URL['produksi']);
        $this->assertDiblokir($u, '/umkm/bahan-baku/create');
        $this->assertDiblokir($u, '/umkm/export/kartu-stok');

        // Modul lain yang "selalu" tetap ada.
        $this->assertTersedia($u, self::URL['produk']);
        $this->assertTersedia($u, self::URL['pembelian']);
        $this->assertTersedia($u, self::URL['penjualan']);
    }

    public function test_service_without_stock_menu_hides_stock_modules_and_uses_service_label(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->ke($u, self::URL['sidebar'])
            ->assertOk()
            ->assertDontSee('/umkm/bahan-baku', false)
            ->assertDontSee('/umkm/kartu-stok', false)
            ->assertSee('Daftar Layanan Jasa')
            ->assertDontSee('Produk Jadi');
    }

    public function test_service_without_stock_does_not_see_inventory_method_and_input_is_ignored(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->ke($u, self::URL['profil'])->assertOk()->assertDontSee('Metode Penilaian Persediaan');

        $this->sebagai($u)->put('/umkm/profile', [
            'name' => 'Pemilik', 'email' => $u->email, 'inventory_method' => 'LIFO',
        ])->assertRedirect(route('umkm.profile'));

        $this->assertSame('Average', $u->fresh()->umkm->inventory_method); // tidak berubah
    }

    public function test_service_without_stock_dashboard_hides_stock_cards(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->ke($u, '/umkm/dashboard')
            ->assertOk()
            ->assertDontSee('Item Bahan Aktif')
            ->assertDontSee('Stok Bahan Menipis');
    }

    public function test_json_request_to_hidden_module_gets_403(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);

        $this->sebagai($u)->getJson(self::URL['bahan'])->assertForbidden();
    }

    // ---------- Jasa dengan persediaan ----------

    public function test_service_with_stock_sees_stock_modules(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => true, 'melakukan_produksi' => false], 'L2');

        $this->assertTersedia($u, self::URL['bahan']);
        $this->assertTersedia($u, self::URL['kartu']); // laporan Berkembang ke atas (Tabel 3.9)
        $this->ke($u, self::URL['profil'])->assertSee('Metode Penilaian Persediaan');
        $this->ke($u, self::URL['sidebar'])->assertSee('/umkm/bahan-baku', false)->assertSee('/umkm/kartu-stok', false);
        $this->ke($u, '/umkm/dashboard')->assertSee('Item Bahan Aktif');
    }

    public function test_service_never_shows_production(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => true, 'melakukan_produksi' => true], 'L3');

        $this->assertDiblokir($u, self::URL['produksi']);
    }

    // ---------- Barang ----------

    public function test_goods_without_production_cannot_use_production_or_raw_materials(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'barang', 'memiliki_persediaan' => true, 'melakukan_produksi' => false], 'L3');

        $this->assertDiblokir($u, self::URL['produksi']);
        $this->assertDiblokir($u, self::URL['bahan']); // keputusan E: bahan baku barang = jika_melakukan_produksi

        // Kartu stok barang: selalu.
        $this->assertTersedia($u, self::URL['kartu']);
        $this->assertTersedia($u, self::URL['produk']);
    }

    public function test_goods_with_production_gets_raw_materials_and_goods_label(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'barang', 'memiliki_persediaan' => true, 'melakukan_produksi' => true], 'L3');

        // Halaman produksi ditutup untuk semua (MVP), walau aturan modul mengizinkan.
        $this->assertDiblokir($u, self::URL['produksi']);
        $this->assertTersedia($u, self::URL['bahan']);
        $this->ke($u, self::URL['sidebar'])->assertSee('Produk')->assertDontSee('Daftar Layanan Jasa');
    }

    public function test_production_also_needs_top_level_even_when_rumpun_allows_it(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'barang', 'memiliki_persediaan' => true, 'melakukan_produksi' => true], 'L2');

        $this->assertDiblokir($u, self::URL['produksi']); // level + rumpun berlaku bersama
    }

    // ---------- UMKM lama (BR-14) ----------

    public function test_legacy_umkm_with_empty_rumpun_loses_no_feature(): void
    {
        $u = $this->buatUmkm([], 'L2'); // rumpun dan flag null

        foreach (['bahan', 'produk', 'pembelian', 'penjualan', 'kasir', 'beban', 'kartu'] as $kunci) {
            $this->assertTersedia($u, self::URL[$kunci]);
        }

        $this->ke($u, self::URL['profil'])->assertSee('Metode Penilaian Persediaan');
        $this->ke($u, '/umkm/dashboard')->assertSee('Item Bahan Aktif')->assertSee('Stok Bahan Menipis');
        $this->ke($u, self::URL['sidebar'])->assertSee('Produk Jadi'); // label v1.0 tetap
    }

    public function test_umkm_with_rumpun_but_empty_flag_is_not_hidden(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa'], 'L2'); // persediaan belum dilengkapi (null)

        $this->assertTersedia($u, self::URL['bahan']);
        $this->assertTersedia($u, self::URL['kartu']);
    }

    // ---------- Data tidak terhapus ----------

    public function test_hiding_a_module_does_not_delete_existing_data(): void
    {
        $u = $this->buatUmkm(['rumpun' => 'jasa', 'memiliki_persediaan' => false, 'melakukan_produksi' => false]);
        $bahan = BahanBaku::create([
            'kode_bahan' => 'BB001', 'umkm_id' => $u->umkm->id, 'nama_bahan' => 'Kain', 'satuan' => 'm', 'stok_awal' => 5,
        ]);

        $this->assertDiblokir($u, self::URL['bahan']);

        $this->assertDatabaseHas('bahan_baku', ['id' => $bahan->id, 'nama_bahan' => 'Kain']);
    }

    // ---------- Otorisasi dasar ----------

    public function test_guest_and_admin_cannot_open_umkm_modules(): void
    {
        $this->get(self::URL['bahan'])->assertRedirect(route('login'));
        $this->sebagai($this->buatAdminK3())->get(self::URL['bahan'])->assertForbidden();
    }
}
