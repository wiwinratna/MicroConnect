<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\Penjualan;
use App\Models\PenilaianLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SiapkanPenilaianUlang;
use Tests\TestCase;

/**
 * FR-24 dan FR-25: Admin meninjau dan menetapkan level; level baru berlaku hanya setelah disimpan.
 */
class TinjauPenilaianUlangTest extends TestCase
{
    use RefreshDatabase, SiapkanPenilaianUlang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK4b();
        $this->buatPeriode(['nama' => 'Penilaian Ulang Uji']);
    }

    /** UMKM level $kodeLevel yang sudah mengirim penilaian ulang dengan jawaban $huruf. */
    private function umkmMengirim(string $huruf, string $kodeLevel = 'L2', array $umkm = []): array
    {
        $u = $this->umkmAktif(array_merge(['nama_usaha' => 'Toko Uji'], $umkm), $kodeLevel);
        $this->kirimUlang($u, $huruf);

        return [$u, $this->ulangDari($u)];
    }

    private function tetapkan(PenilaianLevel $n, string $kodeLevel, array $tambahan = [])
    {
        return $this->sebagai($this->buatAdminK3())->post(route('admin.penilaian-ulang.tetapkan', $n->id), array_merge([
            'level_akhir_id' => $this->levelKode($kodeLevel)->id,
        ], $tambahan));
    }

    // ---------- daftar dan detail ----------

    public function test_list_shows_waiting_by_default_and_finished_in_other_tab(): void
    {
        [$u1, $menunggu] = $this->umkmMengirim('aaaa', 'L2', ['nama_usaha' => 'Toko Menunggu']);
        [$u2, $selesai] = $this->umkmMengirim('aaaa', 'L2', ['nama_usaha' => 'Toko Selesai']);
        $this->tetapkan($selesai, 'L1');
        $this->flushSession(); // buang pesan sukses langkah sebelumnya agar tidak ikut tampil

        $admin = $this->buatAdminK3();
        $this->sebagai($admin)->get(route('admin.penilaian-ulang.index'))
            ->assertOk()->assertSee('Toko Menunggu')->assertDontSee('Toko Selesai')->assertSee('Turun');

        $this->sebagai($admin)->get(route('admin.penilaian-ulang.index', ['status' => 'selesai']))
            ->assertSee('Toko Selesai')->assertDontSee('Toko Menunggu');
    }

    public function test_detail_shows_answers_previous_answers_recommendation_and_system_comparison(): void
    {
        $u = $this->umkmAktif(['nama_usaha' => 'Toko Uji'], 'L2');
        $this->buatPenilaianLama($u, 'abab');
        $this->catatJurnal($u->umkm->id, now()->subDays(4)->toDateString(), '400', 0, 700_000_000); // saran: b
        $this->kirimUlang($u, 'dbab'); // UMKM memilih d (di luar batas mikro), menyimpang dari saran b
        $n = $this->ulangDari($u);

        $this->sebagai($this->buatAdminK3())->get(route('admin.penilaian-ulang.show', $n->id))
            ->assertOk()
            ->assertSee('Toko Uji')
            ->assertSee('Lebih dari Rp2 miliar')              // jawaban sekarang
            ->assertSee('Di luar batas mikro')
            ->assertSee('Lebih dari Rp500 juta sampai dengan Rp1,2 miliar') // saran sistem / sebelumnya
            ->assertSee('Level rekomendasi')
            ->assertSee('Angka dari pembukuan sistem')
            ->assertSee('Diubah UMKM dari saran sistem')
            ->assertSee('Level 2 - Berkembang');                // level saat ini
    }

    public function test_detail_marks_choice_that_matches_system_suggestion(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $this->catatJurnal($u->umkm->id, now()->subDays(4)->toDateString(), '400', 0, 700_000_000);
        $this->kirimUlang($u, 'baab'); // sesuai saran b
        $n = $this->ulangDari($u);

        $this->sebagai($this->buatAdminK3())->get(route('admin.penilaian-ulang.show', $n->id))
            ->assertSee('Sesuai saran sistem');
    }

    // ---------- FR-24 / FR-25: keputusan ----------

    public function test_level_changes_only_after_admin_saves(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3'); // rekomendasi Dasar, turun

        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id); // belum berubah

        $this->tetapkan($n, 'L1')->assertRedirect(route('admin.penilaian-ulang.index'))->assertSessionHas('success');

        $this->assertSame($this->levelKode('L1')->id, $u->umkm->fresh()->level_id);
    }

    public function test_same_as_recommendation_needs_no_reason_and_stores_decision(): void
    {
        [$u, $n] = $this->umkmMengirim('cccc', 'L2'); // naik ke Lanjutan
        $admin = $this->buatAdminK3();

        $this->sebagai($admin)->post(route('admin.penilaian-ulang.tetapkan', $n->id), [
            'level_akhir_id' => $this->levelKode('L3')->id,
            'catatan_peninjauan' => 'Pembukuan rapi.',
            'tindak_lanjut_pembinaan' => 'Pelatihan laporan arus kas.',
        ])->assertSessionHas('success');

        $n->refresh();
        $this->assertSame('selesai', $n->status_peninjauan);
        $this->assertSame($this->levelKode('L3')->id, $n->level_akhir_id);
        $this->assertNull($n->alasan_penetapan);
        $this->assertSame($admin->id, $n->ditinjau_oleh);
        $this->assertNotNull($n->tanggal_peninjauan);
        $this->assertSame('Pembukuan rapi.', $n->catatan_peninjauan);
        $this->assertSame('Pelatihan laporan arus kas.', $n->tindak_lanjut_pembinaan);
        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id);
    }

    public function test_different_level_requires_reason(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3'); // rekomendasi L1

        $this->tetapkan($n, 'L2')->assertSessionHas('error');
        $this->tetapkan($n, 'L2', ['alasan' => '   '])->assertSessionHas('error');

        $this->assertSame('menunggu', $n->fresh()->status_peninjauan);
        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id);
    }

    public function test_different_level_with_reason_is_saved_and_shown_to_umkm(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');

        $this->tetapkan($n, 'L2', ['alasan' => 'Masih dalam masa pembinaan.'])->assertSessionHas('success');

        $n->refresh();
        $this->assertSame('Masih dalam masa pembinaan.', $n->alasan_penetapan);
        $this->assertSame($this->levelKode('L1')->id, $n->level_rekomendasi_id); // rekomendasi tidak berubah
        $this->assertSame($this->levelKode('L2')->id, $u->umkm->fresh()->level_id);

        $this->ke($u, '/umkm/penilaian')->assertOk()
            ->assertSee('Alasan penetapan')->assertSee('Masih dalam masa pembinaan.')->assertSee('Level 2 - Berkembang');
    }

    public function test_reason_is_hidden_from_umkm_when_level_equals_recommendation(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L2');

        $this->tetapkan($n, 'L1', ['alasan' => 'Tidak perlu tampil.']);

        $this->ke($u, '/umkm/penilaian')->assertOk()->assertDontSee('Alasan penetapan')->assertDontSee('Tidak perlu tampil.');
    }

    public function test_result_notification_is_sent_with_reason_only_when_different(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3', ['nama_usaha' => 'Toko Uji']);
        $this->tetapkan($n, 'L1');

        $wa = Notifikasi::where('jenis', 'hasil_penilaian')->where('saluran', 'whatsapp')->first();
        $this->assertSame('Hasil penilaian Toko Uji: level Level 1 - Dasar.', $wa->isi);
        $this->assertSame('terkirim', $wa->status);
        $this->assertSame($u->umkm->id, $wa->umkm_id);
        $this->assertNotNull(Notifikasi::where('jenis', 'hasil_penilaian')->where('saluran', 'aplikasi')->first());

        [$u2, $n2] = $this->umkmMengirim('aaaa', 'L3', ['nama_usaha' => 'Toko Dua']);
        $this->tetapkan($n2, 'L2', ['alasan' => 'Usaha berjalan baik.']);

        $this->assertSame(
            'Hasil penilaian Toko Dua: level Level 2 - Berkembang. Alasan: Usaha berjalan baik.',
            Notifikasi::where('jenis', 'hasil_penilaian')->where('umkm_id', $u2->umkm->id)->where('saluran', 'whatsapp')->value('isi')
        );
    }

    public function test_history_and_transactions_stay_when_level_goes_down(): void
    {
        $u = $this->umkmAktif([], 'L3');
        $awal = $this->buatPenilaianLama($u, 'cccc', 'L3');
        Penjualan::create([
            'umkm_id' => $u->umkm->id, 'tanggal' => now()->subDays(3)->toDateString(),
            'kode_penjualan' => 'PJ-UJI-1', 'pembeli' => 'Umum', 'total' => 75000,
        ]);
        $this->catatJurnal($u->umkm->id, now()->subDays(3)->toDateString(), '400', 0, 75000);

        $this->kirimUlang($u, 'aaaa');
        $this->tetapkan($this->ulangDari($u), 'L1');

        $this->assertSame($this->levelKode('L1')->id, $u->umkm->fresh()->level_id);
        $this->assertSame(1, Penjualan::where('umkm_id', $u->umkm->id)->count());
        $this->assertSame(1, DB::table('jurnal_umum')->where('umkm_id', $u->umkm->id)->count());
        $this->assertSame(2, PenilaianLevel::where('umkm_id', $u->umkm->id)->count()); // awal + ulang
        $this->assertSame(4, $awal->fresh()->detail()->count());

        $this->ke($u, '/umkm/penilaian')->assertSee('Penilaian awal')->assertSee('Penilaian ulang');
    }

    public function test_reviewing_twice_is_refused(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');

        $this->tetapkan($n, 'L1')->assertSessionHas('success');
        $this->tetapkan($n, 'L3', ['alasan' => 'Ubah pikiran.'])->assertSessionHas('error');

        $this->assertSame($this->levelKode('L1')->id, $u->umkm->fresh()->level_id);
        $this->assertSame(2, Notifikasi::where('jenis', 'hasil_penilaian')->count()); // tidak digandakan
    }

    public function test_decision_is_atomic_with_level_change_and_notification(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');

        Notifikasi::creating(function () {
            throw new \RuntimeException('gagal di tengah');
        });

        try {
            $this->tetapkan($n, 'L1')->assertStatus(500);
        } finally {
            Notifikasi::flushEventListeners();
        }

        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id);
        $this->assertSame('menunggu', $n->fresh()->status_peninjauan);
        $this->assertNull($n->fresh()->level_akhir_id);
    }

    public function test_decision_is_written_to_audit_log_with_old_and_new_level(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');
        $admin = $this->buatAdminK3();

        $this->sebagai($admin)->post(route('admin.penilaian-ulang.tetapkan', $n->id), [
            'level_akhir_id' => $this->levelKode('L2')->id, 'alasan' => 'Alasan uji.',
        ]);

        $log = DB::table('log_audit')->where('aksi', 'penilaian_ulang.tetapkan')->first();
        $this->assertSame($admin->id, (int) $log->pengguna_id);
        $this->assertStringContainsString('"level":"L3"', $log->data_lama);
        $this->assertStringContainsString('"level":"L2"', $log->data_baru);
        $this->assertStringContainsString('Alasan uji.', $log->data_baru);
    }

    public function test_unknown_level_is_rejected(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');

        $this->sebagai($this->buatAdminK3())->post(route('admin.penilaian-ulang.tetapkan', $n->id), ['level_akhir_id' => 9999])
            ->assertSessionHasErrors('level_akhir_id');

        $this->assertSame('menunggu', $n->fresh()->status_peninjauan);
    }

    // ---------- otorisasi lewat URL langsung ----------

    public function test_only_admin_can_review(): void
    {
        [$u, $n] = $this->umkmMengirim('aaaa', 'L3');
        $data = ['level_akhir_id' => $this->levelKode('L1')->id];

        auth()->logout(); // penyiapan memakai sesi UMKM; mulai sebagai tamu
        $this->flushSession();

        $this->get(route('admin.penilaian-ulang.index'))->assertRedirect(route('login'));
        $this->post(route('admin.penilaian-ulang.tetapkan', $n->id), $data)->assertRedirect(route('login'));

        $this->sebagai($u)->get(route('admin.penilaian-ulang.index'))->assertRedirect(route('admin.login'));
        $this->sebagai($u)->get(route('admin.penilaian-ulang.show', $n->id))->assertRedirect(route('admin.login'));
        $this->sebagai($u)->post(route('admin.penilaian-ulang.tetapkan', $n->id), $data)->assertRedirect(route('admin.login'));

        $this->assertSame('menunggu', $n->fresh()->status_peninjauan);
        $this->assertSame($this->levelKode('L3')->id, $u->umkm->fresh()->level_id);
    }

    public function test_initial_assessment_cannot_be_reviewed_through_this_screen(): void
    {
        $u = $this->umkmAktif([], 'L2');
        $awal = $this->buatPenilaianLama($u, 'aaaa');

        $this->sebagai($this->buatAdminK3())->get(route('admin.penilaian-ulang.show', $awal->id))->assertNotFound();
        $this->tetapkan($awal, 'L3')->assertNotFound();
        $this->assertSame($this->levelKode('L2')->id, $u->umkm->fresh()->level_id);
    }
}
