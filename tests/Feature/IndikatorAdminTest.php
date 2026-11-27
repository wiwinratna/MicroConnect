<?php

namespace Tests\Feature;

use App\Models\Indikator;
use App\Models\PengajuanPendaftaran;
use App\Models\UmkmLevel;
use Database\Seeders\IndikatorSeeder;
use Database\Seeders\UmkmLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanModul;
use Tests\TestCase;

/** FR-15: Admin mengelola indikator, pilihan jawaban, dan batas skor level. */
class IndikatorAdminTest extends TestCase
{
    use RefreshDatabase, SiapkanModul;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK3();
    }

    public function test_new_question_is_saved_inactive_and_hidden_from_questionnaire(): void
    {
        $this->admin()->post(route('admin.indikator.store'), $this->isian())
            ->assertRedirect(route('admin.indikator.index'))->assertSessionHasNoErrors();

        $baru = Indikator::where('nama', 'Pemasaran')->firstOrFail();
        $this->assertFalse($baru->aktif);
        $this->assertSame([1, 3], $baru->pilihan->pluck('skor')->all());
        $this->assertSame(1, DB::table('log_audit')->where('aksi', 'indikator.buat')->count());

        auth()->logout();
        $this->get(route('register'))->assertDontSee('Bagaimana cara Anda memasarkan?');
    }

    public function test_activation_needs_level_ranges_that_cover_the_new_total(): void
    {
        $this->admin()->post(route('admin.indikator.store'), $this->isian());
        $baru = Indikator::where('nama', 'Pemasaran')->firstOrFail();

        $this->admin()->patch(route('admin.indikator.status', $baru->id))->assertSessionHasErrors('status');
        $this->assertFalse($baru->fresh()->aktif);

        $this->admin()->put(route('admin.indikator.batas'), ['batas' => $this->batas([[4, 7], [8, 11], [12, 15]])])
            ->assertSessionHasNoErrors();
        $this->admin()->patch(route('admin.indikator.status', $baru->id))->assertSessionHasNoErrors();

        $this->assertTrue($baru->fresh()->aktif);
        auth()->logout();
        $this->get(route('register'))->assertSee('Bagaimana cara Anda memasarkan?');
    }

    public function test_level_ranges_with_gap_or_overlap_are_rejected(): void
    {
        foreach ([[[4, 6], [8, 9], [10, 12]], [[4, 7], [7, 9], [10, 12]], [[4, 6], [7, 9], [10, 11]]] as $salah) {
            $this->admin()->put(route('admin.indikator.batas'), ['batas' => $this->batas($salah)])
                ->assertSessionHasErrors('batas');
        }

        $this->assertSame([4, 7, 10], UmkmLevel::orderBy('urutan')->pluck('skor_min')->map(fn ($n) => (int) $n)->all());
    }

    public function test_changing_a_score_keeps_old_assessment_unchanged(): void
    {
        $this->post(route('register.process'), $this->isianDaftar('aaaa'))->assertSessionHasNoErrors();
        $detail = PengajuanPendaftaran::firstOrFail()->penilaian->detail->first();
        $indikator = Indikator::with('pilihan')->findOrFail($detail->indikator_id);

        $pilihan = $indikator->pilihan->map(fn ($p) => [
            'id' => $p->id, 'teks' => $p->teks, 'skor' => $p->id === $detail->pilihan_id ? 2 : $p->skor,
        ])->all();

        $this->admin()->put(route('admin.indikator.batas'), ['batas' => $this->batas([[3, 6], [7, 9], [10, 13]])]);
        $this->admin()->put(route('admin.indikator.update', $indikator->id), [
            'nama' => 'Nama lain', 'pertanyaan' => $indikator->pertanyaan, 'urutan' => 1, 'pilihan' => $pilihan,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, (int) $detail->fresh()->skor);
        $this->assertSame(2, (int) DB::table('pilihan_jawaban')->where('id', $detail->pilihan_id)->value('skor'));
        // Nama indikator omzet terkunci agar pengisian otomatis tetap jalan.
        $this->assertSame('Omzet tahunan', $indikator->fresh()->nama);
        // Rentang angka dari seeder tidak hilang walau tidak dikirim ulang.
        $this->assertNull(DB::table('pilihan_jawaban')->where('id', $detail->pilihan_id)->value('nilai_min'));
    }

    public function test_answered_question_and_choice_cannot_be_deleted(): void
    {
        $this->post(route('register.process'), $this->isianDaftar('aaaa'));
        $kebiasaan = Indikator::with('pilihan')->where('urutan', 4)->firstOrFail();

        $this->admin()->delete(route('admin.indikator.destroy', $kebiasaan->id))->assertSessionHasErrors('status');

        $sisa = $kebiasaan->pilihan->skip(1)->map(fn ($p) => ['id' => $p->id, 'teks' => $p->teks, 'skor' => $p->skor])->values()->all();
        $this->admin()->put(route('admin.indikator.update', $kebiasaan->id), [
            'nama' => $kebiasaan->nama, 'pertanyaan' => $kebiasaan->pertanyaan, 'urutan' => 4, 'pilihan' => $sisa,
        ])->assertSessionHasErrors();

        $this->assertSame(3, $kebiasaan->pilihan()->count());
    }

    public function test_unused_question_can_be_deleted(): void
    {
        $this->admin()->post(route('admin.indikator.store'), $this->isian());
        $baru = Indikator::where('nama', 'Pemasaran')->firstOrFail();

        $this->admin()->delete(route('admin.indikator.destroy', $baru->id))->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('indikator', ['id' => $baru->id]);
        $this->assertDatabaseMissing('pilihan_jawaban', ['indikator_id' => $baru->id]);
    }

    public function test_reserved_name_and_single_choice_are_rejected(): void
    {
        $this->admin()->post(route('admin.indikator.store'), ['nama' => 'Modal tambahan'] + $this->isian())
            ->assertSessionHasErrors('nama');
        $this->admin()->post(route('admin.indikator.store'), ['pilihan' => [['teks' => 'Satu', 'skor' => 1]]] + $this->isian())
            ->assertSessionHasErrors('pilihan');
    }

    public function test_pages_open_for_admin_only(): void
    {
        $this->admin()->get(route('admin.indikator.index'))->assertOk()->assertSee('Batas skor level');
        $this->admin()->get(route('admin.indikator.create'))->assertOk();
        $this->admin()->get(route('admin.indikator.edit', Indikator::firstOrFail()->id))->assertOk()->assertSee('Rupiah, lebih dari');

        $this->sebagai($this->buatUmkm())->get(route('admin.indikator.index'))->assertRedirect();
    }

    public function test_reseeding_does_not_overwrite_admin_changes(): void
    {
        Indikator::where('urutan', 4)->update(['pertanyaan' => 'Diubah admin']);
        UmkmLevel::where('kode', 'L1')->update(['skor_max' => 5]);

        $this->seed(IndikatorSeeder::class);
        $this->seed(UmkmLevelSeeder::class);

        $this->assertSame('Diubah admin', Indikator::where('urutan', 4)->value('pertanyaan'));
        $this->assertSame(5, (int) UmkmLevel::where('kode', 'L1')->value('skor_max'));
    }

    public function test_nib_is_optional_validated_and_stored(): void
    {
        $this->post(route('register.process'), ['nib' => '12345'] + $this->isianDaftar('aaaa'))
            ->assertSessionHasErrors('nib');

        $this->post(route('register.process'), ['nib' => '1234567890123'] + $this->isianDaftar('aaaa'))
            ->assertSessionHasNoErrors();

        $pengajuan = PengajuanPendaftaran::firstOrFail();
        $this->assertSame('1234567890123', $pengajuan->nib);

        config(['minect.initial_password' => 'sandi-uji-123']);
        $this->flushSession(); // galat dari kiriman pertama tidak ikut terbawa
        $this->admin()->post(route('admin.pengajuan.setujui', $pengajuan->id), [
            'level_akhir_id' => $pengajuan->penilaian->level_rekomendasi_id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('umkm', ['nib' => '1234567890123']);
    }

    private function admin()
    {
        return $this->sebagai($this->buatAdminK3());
    }

    private function isian(): array
    {
        return [
            'nama' => 'Pemasaran', 'pertanyaan' => 'Bagaimana cara Anda memasarkan?', 'urutan' => 5,
            'pilihan' => [['teks' => 'Dari mulut ke mulut', 'skor' => 1], ['teks' => 'Lewat media sosial', 'skor' => 3]],
        ];
    }

    /** @param array<int,array{0:int,1:int}> $rentang urut dari level 1 */
    private function batas(array $rentang): array
    {
        return UmkmLevel::orderBy('urutan')->get()->values()
            ->mapWithKeys(fn ($l, $i) => [$l->id => ['min' => $rentang[$i][0], 'max' => $rentang[$i][1]]])->all();
    }

    private function isianDaftar(string $jawaban): array
    {
        $pilihan = Indikator::aktif()->orderBy('urutan')->get()->values()->mapWithKeys(
            fn ($ind, $i) => [$ind->id => $ind->pilihan()->get()->get(ord($jawaban[$i]) - 97)->id]
        )->all();

        return [
            'nama_pemilik' => 'Pemilik Uji', 'email' => 'nib@contoh.test', 'username' => 'pemilik_nib',
            'no_wa' => '081288880001', 'nama_usaha' => 'Usaha NIB', 'alamat' => 'Jl. Uji 1',
            'rumpun' => 'barang', 'mengolah_bahan' => '1',
            'jenis_usaha_id' => \App\Models\JenisUsaha::where('nama', 'like', 'Jasa boga%')->value('id'),
            'jawaban' => $pilihan, 'persetujuan_data_pribadi' => '1',
        ];
    }
}
