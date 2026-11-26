<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\Notifikasi;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\ProdukBahan;
use App\Models\User;
use App\Services\Notifikasi\TemplatPesan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Estimasi HPP pada Daftar Produk, dan kata sandi disamarkan di Kotak Notifikasi Admin. */
class TampilanHppSandiTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
        $this->user = $this->buatUmkm(['rumpun' => 'barang'], 'L3');
    }

    private function bahan(string $kode, string $satuan, array $hargaBeli = []): BahanBaku
    {
        $umkmId = $this->user->umkm->id;
        $bahan = BahanBaku::create([
            'kode_bahan' => $kode, 'umkm_id' => $umkmId, 'nama_bahan' => $kode, 'satuan' => $satuan, 'stok_awal' => 0,
        ]);

        foreach ($hargaBeli as $i => $harga) {
            $p = Pembelian::create([
                'umkm_id' => $umkmId, 'kode_pembelian' => "{$kode}-{$i}", 'tanggal' => '2026-11-01', 'total' => $harga,
            ]);
            PembelianDetail::create([
                'pembelian_id' => $p->id, 'bahan_id' => $bahan->id, 'qty' => 1, 'harga_beli' => $harga, 'subtotal' => $harga,
            ]);
        }

        return $bahan;
    }

    /** @param array<int,array{0:BahanBaku,1:float,2:string}> $resep */
    private function produk(string $nama, array $resep): Produk
    {
        $produk = Produk::create([
            'umkm_id' => $this->user->umkm->id, 'kode_produk' => $nama, 'nama_produk' => $nama, 'harga_jual' => 10000,
        ]);

        foreach ($resep as [$bahan, $qty, $satuan]) {
            ProdukBahan::create(['produk_id' => $produk->id, 'bahan_id' => $bahan->id, 'qty' => $qty, 'satuan' => $satuan]);
        }

        return $produk;
    }

    public function test_estimate_uses_recipe_and_latest_purchase_price(): void
    {
        $tepung = $this->bahan('Tepung', 'kg', [8000, 10000]); // terakhir 10.000
        $kemasan = $this->bahan('Kemasan', 'pcs', [500]);
        $produk = $this->produk('Kue', [[$tepung, 0.5, 'kg'], [$kemasan, 2, 'pcs']]);

        $this->assertEqualsWithDelta(6000, $produk->estimasiHpp(), 0.01);
    }

    public function test_estimate_is_unknown_without_recipe_or_purchase(): void
    {
        $beli = $this->bahan('Gula', 'kg', [12000]);
        $belum = $this->bahan('Ragi', 'pcs');

        $this->assertNull($this->produk('Tanpa Resep', [])->estimasiHpp());
        $this->assertNull($this->produk('Setengah', [[$beli, 1, 'kg'], [$belum, 1, 'pcs']])->estimasiHpp());
    }

    public function test_product_list_shows_estimate_without_stock_column(): void
    {
        $gula = $this->bahan('Gula', 'kg', [12000]);
        $this->produk('Sirup', [[$gula, 0.25, 'kg']]);
        $this->produk('Kosong', []);

        $this->sebagai($this->user)->get(route('umkm.produk.index'))->assertOk()
            ->assertSee(rupiah(3000))->assertSee('Belum ada')->assertDontSee('>Stok</th>', false);
    }

    public function test_estimate_button_still_returns_same_value(): void
    {
        $gula = $this->bahan('Gula', 'kg', [12000]);
        $produk = $this->produk('Sirup', [[$gula, 0.25, 'kg']]);

        $this->sebagai($this->user)->postJson(route('umkm.produk.hitungHpp', $produk))
            ->assertOk()->assertJsonPath('hpp', 3000)->assertJsonPath('detail_bahan.0.harga_unit', 12000);
    }

    public function test_admin_inbox_masks_initial_password_but_keeps_stored_message(): void
    {
        $isi = TemplatPesan::render('akun_aktif', [
            'nama_usaha' => 'Toko A', 'username' => 'toko_a', 'kata_sandi_awal' => 'Rahasia.9x',
        ]);
        $n = Notifikasi::create([
            'no_tujuan' => '628123', 'jenis' => 'akun_aktif', 'saluran' => 'whatsapp', 'isi' => $isi, 'status' => 'terkirim',
        ]);

        $this->sebagai($this->buatAdminK3())->get(route('admin.notifikasi.index'))->assertOk()
            ->assertDontSee('Rahasia.9x')->assertSee('Kata sandi awal: ••••••••.')->assertSee('toko_a');

        $this->assertStringContainsString('Rahasia.9x', $n->fresh()->isi);
    }
}
