<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Satuan produk tersimpan saat tambah dan edit. */
class ProdukSatuanTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
        $this->user = $this->buatUmkm(['rumpun' => 'barang'], 'L3');
    }

    private function tambah(array $ubah = []): Produk
    {
        $this->sebagai($this->user)->post(route('umkm.produk.store'), $ubah + [
            'nama_produk' => 'Keripik', 'satuan_produk' => 'bungkus', 'harga_jual' => 10000,
        ])->assertSessionHasNoErrors();

        return Produk::latest('id')->firstOrFail();
    }

    public function test_unit_is_saved_and_listed(): void
    {
        $this->assertSame('bungkus', $this->tambah()->satuan);

        $this->sebagai($this->user)->get(route('umkm.produk.index'))->assertOk()->assertSee('bungkus');
    }

    public function test_unit_can_be_changed_from_edit_form(): void
    {
        $produk = $this->tambah();

        $this->sebagai($this->user)->get(route('umkm.produk.edit', $produk))
            ->assertOk()->assertSee('name="satuan_produk"', false)->assertSee('bungkus');

        $this->sebagai($this->user)->put(route('umkm.produk.update', $produk), [
            'nama_produk' => 'Keripik', 'satuan_produk' => 'dus', 'harga_jual' => 12000,
            // Baris resep memakai nama `satuan[]` dan tidak boleh menimpa satuan produk.
            'bahan_id' => [null], 'qty' => [null], 'satuan' => [null],
        ])->assertSessionHasNoErrors();

        $this->assertSame('dus', $produk->fresh()->satuan);
    }

    public function test_update_without_unit_field_keeps_old_value_and_too_long_is_rejected(): void
    {
        $produk = $this->tambah();

        $this->sebagai($this->user)->put(route('umkm.produk.update', $produk), [
            'nama_produk' => 'Keripik Baru', 'harga_jual' => 12000,
        ]);
        $this->assertSame('bungkus', $produk->fresh()->satuan);

        $this->sebagai($this->user)->put(route('umkm.produk.update', $produk), [
            'nama_produk' => 'Keripik', 'satuan_produk' => str_repeat('a', 51),
        ])->assertSessionHasErrors('satuan_produk');
    }
}
