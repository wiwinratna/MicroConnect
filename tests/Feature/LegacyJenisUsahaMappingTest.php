<?php

namespace Tests\Feature;

use Database\Seeders\JenisUsahaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * K0: pemetaan jenis_usaha lama ke rumpun (barang/jasa), BR-14.
 */
class LegacyJenisUsahaMappingTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_11_25_000007_map_legacy_jenis_usaha_to_rumpun.php');
    }

    private function buatUmkm(string $teks, int $nomor, array $ekstra = []): int
    {
        $userId = DB::table('users')->insertGetId([
            'name' => "Pemilik {$nomor}", 'email' => "u{$nomor}@example.test",
            'password' => 'x', 'user_group' => 'pelakuusaha',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('umkm')->insertGetId(array_merge([
            'kode_umkm' => sprintf('UM%05d', $nomor), 'user_id' => $userId,
            'jenis_usaha' => $teks, 'created_at' => now(), 'updated_at' => now(),
        ], $ekstra));
    }

    public function test_legacy_text_maps_to_rumpun_and_leaves_flags_null(): void
    {
        $this->seed(JenisUsahaSeeder::class);

        $ids = [];
        foreach (['Kuliner', 'Makanan & Minuman', 'Perdagangan', 'Fashion & Tekstil', 'Manufaktur', 'Jasa', 'Lainnya'] as $i => $teks) {
            $ids[$teks] = $this->buatUmkm($teks, $i + 1);
        }

        $this->migration()->up();

        $row = fn (string $teks) => DB::table('umkm')->where('id', $ids[$teks])->first();

        $this->assertSame('barang', $row('Kuliner')->rumpun);
        $this->assertSame('barang', $row('Makanan & Minuman')->rumpun);
        $this->assertSame('barang', $row('Perdagangan')->rumpun);
        $this->assertSame('barang', $row('Fashion & Tekstil')->rumpun);
        $this->assertSame('barang', $row('Manufaktur')->rumpun);
        $this->assertNull($row('Manufaktur')->jenis_usaha_id);
        $this->assertSame('jasa', $row('Jasa')->rumpun);
        $this->assertStringStartsWith('Jasa boga', DB::table('jenis_usaha')->where('id', $row('Kuliner')->jenis_usaha_id)->value('nama'));

        // Teks tidak dikenal: tidak disentuh (BR-14: tidak ada yang disembunyikan).
        $this->assertNull($row('Lainnya')->rumpun);

        // Flag tidak diisi oleh pemetaan.
        foreach ($ids as $id) {
            $r = DB::table('umkm')->where('id', $id)->first();
            $this->assertNull($r->memiliki_persediaan);
            $this->assertNull($r->melakukan_produksi);
        }
    }

    public function test_rumpun_already_filled_by_admin_is_not_overwritten(): void
    {
        $this->seed(JenisUsahaSeeder::class);
        $id = $this->buatUmkm('Kuliner', 1, ['rumpun' => 'jasa']);

        $this->migration()->up();

        $this->assertSame('jasa', DB::table('umkm')->where('id', $id)->value('rumpun'));
    }

    public function test_down_reverts_only_rows_not_completed_by_admin(): void
    {
        $this->seed(JenisUsahaSeeder::class);
        $belum    = $this->buatUmkm('Kuliner', 1);
        $dilengkapi = $this->buatUmkm('Perdagangan', 2);

        $this->migration()->up();
        DB::table('umkm')->where('id', $dilengkapi)->update(['memiliki_persediaan' => true, 'melakukan_produksi' => false]);

        $this->migration()->down();

        $this->assertNull(DB::table('umkm')->where('id', $belum)->value('rumpun'));
        $this->assertSame('barang', DB::table('umkm')->where('id', $dilengkapi)->value('rumpun'));
    }
}
