<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** Perubahan level manual: alasan wajib, tercatat, dan UMKM diberi tahu (BR-01, FR-26). */
class UbahLevelAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    private function ubah(int $umkmId, array $data)
    {
        return $this->sebagai($this->buatAdminK3())->put(route('admin.umkm.updateLevel', $umkmId), $data);
    }

    public function test_reason_is_required(): void
    {
        $u = $this->buatUmkm([], 'L1');

        $this->ubah($u->umkm->id, ['level_id' => $this->levelKode('L3')->id])->assertSessionHasErrors('alasan');
        $this->ubah($u->umkm->id, ['level_id' => $this->levelKode('L3')->id, 'alasan' => '   '])
            ->assertSessionHasErrors('alasan');

        $this->assertSame($this->levelKode('L1')->id, $u->umkm->fresh()->level_id);
    }

    public function test_change_is_saved_audited_and_notified(): void
    {
        $u = $this->buatUmkm([], 'L1');

        $this->ubah($u->umkm->id, ['level_id' => $this->levelKode('L2')->id, 'alasan' => 'Hasil kunjungan lapangan'])
            ->assertSessionHas('success');

        $this->assertSame($this->levelKode('L2')->id, $u->umkm->fresh()->level_id);

        $log = DB::table('log_audit')->where('aksi', 'umkm.ubah_level')->where('entitas_id', $u->umkm->id)->first();
        $this->assertNotNull($log->pengguna_id);
        $this->assertStringContainsString('"L1"', $log->data_lama);
        $this->assertStringContainsString('"L2"', $log->data_baru);
        $this->assertStringContainsString('Hasil kunjungan lapangan', $log->data_baru);

        $pesan = Notifikasi::where('umkm_id', $u->umkm->id)->where('saluran', 'aplikasi')->value('isi');
        $this->assertStringContainsString('Hasil kunjungan lapangan', $pesan);
    }

    public function test_same_level_is_rejected(): void
    {
        $u = $this->buatUmkm([], 'L2');

        $this->ubah($u->umkm->id, ['level_id' => $this->levelKode('L2')->id, 'alasan' => 'x'])
            ->assertSessionHasErrors('level_id');

        $this->assertSame(0, DB::table('log_audit')->where('aksi', 'umkm.ubah_level')->count());
    }

    public function test_umkm_cannot_change_its_own_level(): void
    {
        $u = $this->buatUmkm([], 'L1');

        $this->sebagai($u)->put(route('admin.umkm.updateLevel', $u->umkm->id), [
            'level_id' => $this->levelKode('L3')->id, 'alasan' => 'x',
        ]);

        $this->assertSame($this->levelKode('L1')->id, $u->umkm->fresh()->level_id);
    }
}
