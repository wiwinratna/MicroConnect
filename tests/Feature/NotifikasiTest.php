<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\Umkm;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\Notifikasi\NotifikasiDriver;
use App\Services\Notifikasi\SimulasiDriver;
use App\Services\Notifikasi\TemplatPesan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Tests\Concerns\SiapkanPengajuan;
use Tests\TestCase;

/**
 * FR-06, FR-12: notifikasi satu arah dengan driver simulasi.
 */
class NotifikasiTest extends TestCase
{
    use RefreshDatabase, SiapkanPengajuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanK2();
    }

    private function driverGagal(string $pesan = 'gateway mati'): void
    {
        $this->app->bind(NotifikasiDriver::class, fn () => new class($pesan) implements NotifikasiDriver {
            public function __construct(private string $pesan) {}

            public function kirim(Notifikasi $notifikasi): void
            {
                throw new \RuntimeException($this->pesan);
            }
        });
    }

    private function baris(string $jenis, string $saluran): ?Notifikasi
    {
        return Notifikasi::where('jenis', $jenis)->where('saluran', $saluran)->latest('id')->first();
    }

    // ---------- FR-06: registrasi diterima ----------

    public function test_registration_writes_whatsapp_and_app_rows(): void
    {
        $p = $this->ajukan();

        $this->assertSame(2, Notifikasi::count());

        $wa = $this->baris('registrasi_diterima', 'whatsapp');
        $this->assertSame('terkirim', $wa->status);
        $this->assertSame(1, $wa->percobaan);
        $this->assertNotNull($wa->dikirim_pada);
        $this->assertSame('6281234567890', $wa->no_tujuan);
        $this->assertSame($p->id, $wa->pengajuan_id);
        $this->assertNull($wa->umkm_id); // calon belum punya akun
        $this->assertSame(
            'Halo Budi Santoso, registrasi Warung Budi di MINECT telah kami terima. Mohon menunggu verifikasi dari Admin KADIN.',
            $wa->isi
        );

        $app = $this->baris('registrasi_diterima', 'aplikasi');
        $this->assertSame('terkirim', $app->status);
        $this->assertSame($wa->isi, $app->isi);
    }

    public function test_whatsapp_failure_keeps_app_copy_and_does_not_break_registration(): void
    {
        $this->driverGagal();

        $this->post('/register', $this->payload())->assertRedirect(route('register.selesai'));

        $wa = $this->baris('registrasi_diterima', 'whatsapp');
        $this->assertSame('gagal', $wa->status);
        $this->assertSame('gateway mati', $wa->pesan_galat);
        $this->assertSame(1, $wa->percobaan);

        $this->assertSame('terkirim', $this->baris('registrasi_diterima', 'aplikasi')->status);
        $this->assertSame(1, \App\Models\PengajuanPendaftaran::count()); // pendaftaran tetap tersimpan
    }

    public function test_default_driver_is_simulation_and_whatsapp_driver_is_a_failing_skeleton(): void
    {
        $this->assertInstanceOf(SimulasiDriver::class, app(NotifikasiDriver::class));

        config(['minect.notif_driver' => 'whatsapp']);
        $this->ajukan();

        $wa = $this->baris('registrasi_diterima', 'whatsapp');
        $this->assertSame('gagal', $wa->status);
        $this->assertStringContainsString('belum dikonfigurasi', $wa->pesan_galat);
        $this->assertNotNull($this->baris('registrasi_diterima', 'aplikasi'));
    }

    // ---------- FR-12: akun aktif dan penolakan ----------

    public function test_approval_sends_account_active_with_password_only_in_whatsapp_copy(): void
    {
        $p = $this->ajukan();
        $this->setujui($p, $this->level('L1'));

        $umkm = Umkm::firstOrFail();

        $wa = $this->baris('akun_aktif', 'whatsapp');
        $this->assertSame(
            'Akun MINECT Warung Budi telah aktif. Username: budi_baru. Kata sandi awal: ' . self::SANDI_AWAL . '. Silakan ganti kata sandi saat login pertama.',
            $wa->isi
        );
        $this->assertSame('terkirim', $wa->status);
        $this->assertSame($umkm->id, $wa->umkm_id);
        $this->assertSame($p->id, $wa->pengajuan_id);
        $this->assertSame('6281234567890', $wa->no_tujuan);

        $app = $this->baris('akun_aktif', 'aplikasi');
        $this->assertStringContainsString('Username: budi_baru', $app->isi);
        $this->assertStringNotContainsString(self::SANDI_AWAL, $app->isi);
        $this->assertSame($umkm->id, $app->umkm_id);
    }

    public function test_rejection_sends_reason_to_applicant(): void
    {
        $p = $this->ajukan();

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.pengajuan.tolak', $p->id), ['alasan_penolakan' => 'Data tidak jelas.']);

        $wa = $this->baris('pengajuan_ditolak', 'whatsapp');
        $this->assertSame('Pengajuan Warung Budi belum dapat disetujui. Alasan: Data tidak jelas. Anda dapat mendaftar ulang.', $wa->isi);
        $this->assertNull($wa->umkm_id);
        $this->assertSame($p->id, $wa->pengajuan_id);
        $this->assertNotNull($this->baris('pengajuan_ditolak', 'aplikasi'));
    }

    public function test_notifications_are_written_atomically_with_the_decision(): void
    {
        $p = $this->ajukan();
        $sebelum = Notifikasi::count();

        Umkm::creating(function () {
            throw new \RuntimeException('gagal di tengah');
        });

        try {
            $this->setujui($p, $this->level('L1'))->assertStatus(500);
        } finally {
            Umkm::flushEventListeners();
        }

        $this->assertSame($sebelum, Notifikasi::count());
        $this->assertNull($this->baris('akun_aktif', 'whatsapp'));
        $this->assertSame('menunggu', $p->fresh()->status);
    }

    public function test_approving_twice_does_not_duplicate_notifications(): void
    {
        $p = $this->ajukan();
        $admin = $this->buatAdmin();

        $this->setujui($p, $this->level('L1'), null, $admin);
        $this->setujui($p, $this->level('L1'), null, $admin)->assertSessionHas('error');

        $this->assertSame(2, Notifikasi::where('jenis', 'akun_aktif')->count()); // 1 whatsapp + 1 aplikasi
    }

    // ---------- NotificationService ----------

    public function test_number_missing_writes_app_copy_only(): void
    {
        $baris = app(NotificationService::class)->catat('hasil_penilaian', [
            'nama_usaha' => 'Warung Budi', 'level_akhir' => 'Level 2 - Berkembang', 'alasan_jika_berbeda' => '',
        ], '', null, null);

        $this->assertSame([], $baris);
        $this->assertSame(1, Notifikasi::count());
        $this->assertSame('aplikasi', Notifikasi::first()->saluran);
        $this->assertSame('-', Notifikasi::first()->no_tujuan);
    }

    public function test_processing_is_idempotent(): void
    {
        $this->ajukan();
        $wa = $this->baris('registrasi_diterima', 'whatsapp');

        app(NotificationService::class)->proses($wa->id);
        app(NotificationService::class)->proses($wa->id);

        $this->assertSame(1, $wa->fresh()->percobaan);
    }

    public function test_simulation_driver_does_not_log_message_body(): void
    {
        Log::spy();

        (new SimulasiDriver())->kirim(new Notifikasi([
            'jenis' => 'akun_aktif', 'no_tujuan' => '62811', 'isi' => 'Kata sandi awal: RAHASIA-BESAR',
        ]));

        Log::shouldHaveReceived('info')->once()->withArgs(
            fn ($pesan, $konteks) => !str_contains(json_encode($konteks), 'RAHASIA-BESAR') && !str_contains($pesan, 'RAHASIA-BESAR')
        );
    }

    public function test_templates_match_master_prompt_section_8(): void
    {
        $this->assertSame(
            'Halo A, registrasi B di MINECT telah kami terima. Mohon menunggu verifikasi dari Admin KADIN.',
            TemplatPesan::render('registrasi_diterima', ['nama_pemilik' => 'A', 'nama_usaha' => 'B'])
        );
        $this->assertSame(
            'Periode penilaian ulang MINECT dibuka 01/12/2026–15/12/2026. Silakan isi penilaian melalui menu Penilaian.',
            TemplatPesan::render('periode_dibuka', ['tanggal_mulai' => '01/12/2026', 'tanggal_selesai' => '15/12/2026'])
        );
        $this->assertSame(
            'Hasil penilaian B: level Berkembang. Alasan: usaha rapi.',
            TemplatPesan::render('hasil_penilaian', ['nama_usaha' => 'B', 'level_akhir' => 'Berkembang', 'alasan_jika_berbeda' => 'Alasan: usaha rapi.'])
        );
        $this->assertSame(
            'Hasil penilaian B: level Dasar.',
            TemplatPesan::render('hasil_penilaian', ['nama_usaha' => 'B', 'level_akhir' => 'Dasar', 'alasan_jika_berbeda' => ''])
        );
    }

    public function test_reason_ending_with_period_does_not_produce_double_period(): void
    {
        $this->assertSame(
            'Pengajuan B belum dapat disetujui. Alasan: Data tidak jelas. Anda dapat mendaftar ulang.',
            TemplatPesan::render('pengajuan_ditolak', ['nama_usaha' => 'B', 'alasan' => 'Data tidak jelas.'])
        );
        $this->assertSame(
            'Pengajuan B belum dapat disetujui. Alasan: Data tidak jelas. Anda dapat mendaftar ulang.',
            TemplatPesan::render('pengajuan_ditolak', ['nama_usaha' => 'B', 'alasan' => 'Data tidak jelas'])
        );
    }

    public function test_unknown_notification_type_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TemplatPesan::render('jenis_siluman', []);
    }
}
