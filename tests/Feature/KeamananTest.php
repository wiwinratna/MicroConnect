<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\Pelanggan;
use App\Models\Pembelian;
use App\Models\Piutang;
use App\Models\Produk;
use App\Models\ProdukBahan;
use App\Models\Umkm;
use App\Models\User;
use App\Services\PenjualanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Perbaikan temuan cek keamanan 2026-11-26. */
class KeamananTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    private User $saya;
    private User $lain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
        $this->saya = $this->buatUmkm(['rumpun' => 'barang'], 'L3');
        $this->lain = $this->buatUmkm(['rumpun' => 'barang'], 'L3');
    }

    private function bahan(User $milik, string $kode): BahanBaku
    {
        return BahanBaku::create([
            'kode_bahan' => $kode, 'umkm_id' => $milik->umkm->id, 'nama_bahan' => "Rahasia {$kode}",
            'satuan' => 'kg', 'stok_awal' => 5,
        ]);
    }

    private function pelanggan(User $milik): Pelanggan
    {
        return Pelanggan::create(['umkm_id' => $milik->umkm->id, 'nama_pelanggan' => 'Pelanggan Orang Lain']);
    }

    // ---------- 1: pembelian ----------

    public function test_purchase_cannot_use_another_business_material(): void
    {
        $korban = $this->bahan($this->lain, 'BBX');

        $this->sebagai($this->saya)->post(route('umkm.pembelian.store'), [
            'tanggal' => '2026-11-20', 'bahan_id' => [$korban->id], 'qty' => [100], 'harga_beli' => [1],
        ])->assertSessionHasErrors('bahan_id.0');

        $this->assertEquals(5, $korban->fresh()->stok_awal);
        $this->assertSame(0, Pembelian::count());
    }

    public function test_purchase_with_own_material_still_works(): void
    {
        $milik = $this->bahan($this->saya, 'BBS');

        $this->sebagai($this->saya)->post(route('umkm.pembelian.store'), [
            'tanggal' => '2026-11-20', 'bahan_id' => [$milik->id], 'qty' => [2], 'harga_beli' => [1000],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(7, $milik->fresh()->stok_awal);
    }

    // ---------- 2: penjualan kredit ----------

    public function test_credit_sale_cannot_point_to_another_business_customer(): void
    {
        $korban = $this->pelanggan($this->lain);
        $produk = Produk::create([
            'umkm_id' => $this->saya->umkm->id, 'kode_produk' => 'P1', 'nama_produk' => 'Barang', 'harga_jual' => 1000,
        ]);

        $this->sebagai($this->saya)->post(route('umkm.penjualan.store'), [
            'tanggal' => '2026-11-20', 'metode_pembayaran' => 'piutang', 'pelanggan_id' => $korban->id,
            'jatuh_tempo' => '2026-12-01', 'produk_id' => [$produk->id], 'qty' => [1],
        ])->assertSessionHasErrors('pelanggan_id');
        $this->assertSame(0, Piutang::count());

        // Lapis kedua: service menolak walau dipanggil langsung.
        try {
            app(PenjualanService::class)->prosesTransaksi(
                Umkm::find($this->saya->umkm->id), [['produk_id' => $produk->id, 'qty' => 1]],
                '2026-11-20', 'piutang', $korban->id, '2026-12-01'
            );
            $this->fail('Service seharusnya menolak pelanggan milik UMKM lain.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertSame(0, Piutang::count());
        }
    }

    // ---------- 5: resep ----------

    public function test_recipe_cannot_use_another_business_material(): void
    {
        $korban = $this->bahan($this->lain, 'BBY');

        $this->sebagai($this->saya)->post(route('umkm.produk.store'), [
            'nama_produk' => 'Intip', 'satuan_produk' => 'pcs', 'bahan_id' => [$korban->id], 'qty' => [1],
        ])->assertSessionHasErrors('bahan_id.0');

        $this->assertSame(0, Produk::count());
    }

    public function test_old_foreign_recipe_row_reveals_nothing(): void
    {
        $korban = $this->bahan($this->lain, 'BBZ');
        $produk = Produk::create([
            'umkm_id' => $this->saya->umkm->id, 'kode_produk' => 'P9', 'nama_produk' => 'Lama',
        ]);
        ProdukBahan::create(['produk_id' => $produk->id, 'bahan_id' => $korban->id, 'qty' => 1, 'satuan' => 'kg']);

        $this->assertSame([], $produk->rincianBiayaBahan());
        $this->assertNull($produk->estimasiHpp());
    }

    // ---------- 3: batas percobaan login ----------

    private function coba(string $rute, string $email, string $sandi = 'salah-sekali')
    {
        return $this->post(route($rute), ['email' => $email, 'password' => $sandi]);
    }

    public function test_login_is_blocked_after_five_attempts_per_email(): void
    {
        foreach (['umkm.login.process', 'admin.login.process'] as $i => $rute) {
            $email = "tebak{$i}@example.test";

            foreach (range(1, 5) as $n) {
                $this->coba($rute, $email);
            }

            $this->coba($rute, $email)->assertSessionHasErrors('email');
            $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'), $rute);
        }
    }

    public function test_correct_password_is_also_blocked_while_limited(): void
    {
        foreach (range(1, 5) as $n) {
            $this->coba('umkm.login.process', $this->saya->email);
        }

        $this->coba('umkm.login.process', $this->saya->email, 'rahasia123');

        $this->assertGuest();
    }

    public function test_spraying_many_emails_from_one_address_is_blocked(): void
    {
        foreach (range(1, 20) as $n) {
            $this->coba('umkm.login.process', "acak{$n}@example.test");
        }

        $this->coba('umkm.login.process', 'acak21@example.test');

        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
    }

    // ---------- 4: unggah logo ----------

    public function test_logo_extension_comes_from_content_not_file_name(): void
    {
        Storage::fake('public');
        $gambar = UploadedFile::fake()->image('asli.png');
        $jahat = new UploadedFile($gambar->getRealPath(), 'halaman.html', 'image/png', null, true);

        $this->sebagai($this->saya)->put(route('umkm.profile.update'), [
            'name' => 'Pemilik', 'email' => $this->saya->email, 'logo' => $jahat,
        ])->assertSessionHasNoErrors();

        $path = $this->saya->umkm->fresh()->logo_path;
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    // ---------- 6: ganti sandi lewat Profil ----------

    private function profil(array $ubah)
    {
        return $this->sebagai($this->saya)->put(
            route('umkm.profile.update'), ['name' => 'Pemilik', 'email' => $this->saya->email] + $ubah
        );
    }

    public function test_profile_password_change_needs_current_password(): void
    {
        $baru = ['password' => 'sandibaru1', 'password_confirmation' => 'sandibaru1'];

        $this->profil($baru)->assertSessionHasErrors('current_password');
        $this->profil($baru + ['current_password' => 'keliru123'])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('rahasia123', $this->saya->fresh()->password));

        $this->profil($baru + ['current_password' => 'rahasia123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('sandibaru1', $this->saya->fresh()->password));
    }

    public function test_profile_can_be_saved_without_changing_password(): void
    {
        $this->profil(['nama_usaha' => 'Nama Baru'])->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $this->saya->umkm->fresh()->nama_usaha);
        $this->sebagai($this->saya)->get(route('umkm.profile'))->assertSee('name="current_password"', false);
    }
}
