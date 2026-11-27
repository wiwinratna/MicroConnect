<?php

namespace Tests\Feature;

use App\Models\DetailPenilaian;
use App\Models\PengajuanPendaftaran;
use App\Models\PenilaianLevel;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\DataPendaftaran;
use Tests\TestCase;

/**
 * FR-01, FR-02, FR-03, FR-05: pendaftaran mandiri calon UMKM.
 */
class PendaftaranTest extends TestCase
{
    use RefreshDatabase, DataPendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedK1();
    }

    private function kirim(array $ubah = [], string $huruf = 'aaaa')
    {
        return $this->post('/register', $this->payload($ubah, $huruf));
    }

    private function buatAkun(array $user = [], array $umkm = []): User
    {
        $u = User::create(array_merge([
            'name' => 'Akun Lama', 'email' => 'lama@example.test',
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
        ], $user));

        Umkm::create(array_merge(['kode_umkm' => 'UM00099', 'user_id' => $u->id], $umkm));

        return $u;
    }

    // ---------- tampilan ----------

    public function test_form_shows_questions_options_and_examples(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Berapa hasil penjualan (omzet) usaha Anda dalam satu tahun terakhir?')
            ->assertSee('Lebih dari Rp2 miliar')
            ->assertSee('Contoh: menghitung keuntungan dari sisa uang')
            ->assertSee('Jasa boga');
    }

    // ---------- FR-01 ----------

    public function test_valid_submission_saves_pengajuan_without_account(): void
    {
        $this->kirim()->assertRedirect(route('register.selesai'));

        $this->assertSame(1, PengajuanPendaftaran::count());
        $this->assertSame(1, PenilaianLevel::count());
        $this->assertSame(4, DetailPenilaian::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Umkm::count());

        $p = PengajuanPendaftaran::first();
        $this->assertSame('menunggu', $p->status);
        $this->assertSame('62' . '81234567890', $p->no_wa); // 08... dinormalisasi
        $this->assertNull($p->umkm_id);

        $nilai = PenilaianLevel::first();
        $this->assertSame('awal', $nilai->jenis);
        $this->assertSame('baru', $nilai->arah_rekomendasi);
        $this->assertNull($nilai->level_akhir_id);
        $this->assertSame($p->id, $nilai->pengajuan_id);
    }

    public function test_missing_required_fields_are_rejected_and_nothing_is_saved(): void
    {
        $this->post('/register', [])->assertSessionHasErrors([
            'nama_pemilik', 'email', 'no_wa', 'nama_usaha', 'alamat',
            'jenis_usaha_id', 'jawaban', 'persetujuan_data_pribadi',
        ]);

        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_consent_must_be_checked(): void
    {
        $this->kirim(['persetujuan_data_pribadi' => null])
            ->assertSessionHasErrors('persetujuan_data_pribadi');

        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_duplicates_with_existing_accounts_are_rejected(): void
    {
        $this->buatAkun(
            ['email' => 'dipakai@example.test', 'username' => 'dipakai', 'no_wa' => '6281111111111']
        );

        $this->kirim(['email' => 'dipakai@example.test'])->assertSessionHasErrors('email');
        $this->kirim(['no_wa' => '0811-1111-1111'])->assertSessionHasErrors('no_wa');

        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_whatsapp_used_by_legacy_umkm_is_rejected(): void
    {
        $this->buatAkun([], ['no_whatsapp' => '08111000001']);

        $this->kirim(['no_wa' => '+62 811 1000 001'])->assertSessionHasErrors('no_wa');
    }

    public function test_duplicate_with_waiting_pengajuan_is_rejected(): void
    {
        $this->kirim()->assertRedirect(route('register.selesai'));

        $this->kirim()->assertSessionHasErrors(['email', 'no_wa']);
        $this->assertSame(1, PengajuanPendaftaran::count());
    }

    // ---------- FR-02 ----------

    public static function kategoriSesuaiJenis(): array
    {
        return [
            'Pertanian'          => ['Pertanian', 'barang'],
            'Jasa boga'          => ['Jasa boga', 'barang'],
            'Perdagangan barang' => ['Perdagangan barang', 'barang'],
            'Jasa perorangan'    => ['Jasa perorangan', 'jasa'],
            'Kreatif barang'     => ['Usaha kreatif', 'barang'],
            'Kreatif jasa'       => ['Usaha kreatif', 'jasa'],
        ];
    }

    #[DataProvider('kategoriSesuaiJenis')]
    public function test_chosen_business_type_and_category_are_saved(string $awalan, string $jenis): void
    {
        $id = $this->jenisId($awalan);

        $this->kirim([
            'rumpun' => $jenis, 'jenis_usaha_id' => $id, 'mengolah_bahan' => '1', 'punya_persediaan' => '1',
        ])->assertRedirect(route('register.selesai'));

        $p = PengajuanPendaftaran::first();
        $this->assertSame($jenis, $p->rumpun);
        $this->assertSame($id, $p->jenis_usaha_id);
    }

    public function test_business_type_is_required_and_limited_to_goods_or_service(): void
    {
        $this->kirim(['rumpun' => null])->assertSessionHasErrors('rumpun');
        $this->kirim(['rumpun' => 'lainnya'])->assertSessionHasErrors('rumpun');

        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_category_of_other_business_type_is_rejected(): void
    {
        $this->kirim(['rumpun' => 'barang', 'jenis_usaha_id' => $this->jenisId('Jasa perorangan')])
            ->assertSessionHasErrors('jenis_usaha_id');
        $this->kirim(['rumpun' => 'jasa', 'jenis_usaha_id' => $this->jenisId('Pertanian'), 'punya_persediaan' => '0'])
            ->assertSessionHasErrors('jenis_usaha_id');

        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_goods_business_always_has_stock_and_production_from_answer(): void
    {
        $this->kirim(['mengolah_bahan' => '0'])->assertRedirect(route('register.selesai'));

        $p = PengajuanPendaftaran::first();
        $this->assertTrue($p->memiliki_persediaan);
        $this->assertFalse($p->melakukan_produksi);
    }

    public function test_service_business_stock_follows_answer(): void
    {
        foreach ([['1', true], ['0', false]] as [$jawab, $harapan]) {
            PengajuanPendaftaran::query()->delete();

            $this->kirim([
                'rumpun' => 'jasa',
                'jenis_usaha_id' => $this->jenisId('Jasa perorangan'),
                'punya_persediaan' => $jawab,
            ])->assertRedirect(route('register.selesai'));

            $this->assertSame($harapan, PengajuanPendaftaran::first()->memiliki_persediaan);
        }
    }

    public function test_follow_up_question_is_required(): void
    {
        $this->kirim(['mengolah_bahan' => null])->assertSessionHasErrors('mengolah_bahan');

        $this->kirim([
            'rumpun' => 'jasa', 'jenis_usaha_id' => $this->jenisId('Jasa perorangan'), 'punya_persediaan' => null,
        ])->assertSessionHasErrors('punya_persediaan');
    }

    // ---------- FR-03 ----------

    public function test_unanswered_question_is_rejected(): void
    {
        $jawaban = $this->jawab('aaaa');
        array_pop($jawaban);

        $this->kirim(['jawaban' => $jawaban])->assertSessionHasErrors();
        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    public function test_option_belonging_to_other_question_is_rejected(): void
    {
        $jawaban = $this->jawab('aaaa');
        $ids = array_keys($jawaban);
        $jawaban[$ids[0]] = $jawaban[$ids[1]];

        $this->kirim(['jawaban' => $jawaban])->assertSessionHasErrors();
        $this->assertSame(0, PengajuanPendaftaran::count());
    }

    // ---------- FR-04 / BR-11 pada pendaftaran ----------

    public function test_option_d_marks_pengajuan_for_admin_decision(): void
    {
        $this->kirim([], 'daaa')->assertRedirect(route('register.selesai'));

        $this->assertTrue(PengajuanPendaftaran::first()->perlu_keputusan_admin);
        $this->assertSame(6, PenilaianLevel::first()->total_skor);
    }

    public function test_detail_keeps_snapshot_of_text_and_score(): void
    {
        $this->kirim([], 'cbab')->assertRedirect(route('register.selesai'));

        $detail = DetailPenilaian::orderBy('id')->get();
        $this->assertSame([3, 2, 1, 2], $detail->pluck('skor')->all());
        $this->assertSame('Lebih dari Rp1,2 miliar sampai dengan Rp2 miliar', $detail[0]->teks_pilihan);
    }

    // ---------- FR-05 ----------

    public function test_confirmation_page_shows_no_score_or_level(): void
    {
        // Semua jawaban c (skor 12, level Lanjutan): layar konfirmasi tidak boleh menyebutnya.
        $respon = $this->followingRedirects()->post('/register', $this->payload([], 'cccc'));

        $respon->assertOk()->assertSee('Pendaftaran diterima');
        $isi = strtolower($respon->getContent());
        $this->assertStringNotContainsString('skor', $isi);
        $this->assertStringNotContainsString('level', $isi);
        $this->assertStringNotContainsString('lanjut', $isi);
        $this->assertStringNotContainsString('dasar', $isi);
    }

    public function test_confirmation_page_redirects_when_opened_directly(): void
    {
        $this->get('/register/selesai')->assertRedirect(route('register'));
    }

    public function test_double_submit_creates_only_one_pengajuan(): void
    {
        $this->kirim();
        $this->kirim();

        $this->assertSame(1, PengajuanPendaftaran::count());
        $this->assertSame(1, PenilaianLevel::count());
        $this->assertSame(4, DetailPenilaian::count());
    }

    public function test_failure_midway_leaves_no_partial_data(): void
    {
        DetailPenilaian::creating(function () {
            throw new \RuntimeException('gagal di tengah');
        });

        try {
            $this->kirim()->assertStatus(500);
        } finally {
            DetailPenilaian::flushEventListeners();
        }

        $this->assertSame(0, PengajuanPendaftaran::count());
        $this->assertSame(0, PenilaianLevel::count());
        $this->assertSame(0, DetailPenilaian::count());
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [])->assertStatus(302);
        }

        $this->post('/register', [])->assertStatus(429);
    }
}
