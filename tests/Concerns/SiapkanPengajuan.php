<?php

namespace Tests\Concerns;

use App\Models\PengajuanPendaftaran;
use App\Models\UmkmLevel;
use App\Models\User;

/** Pembantu tes K2: Admin, pengajuan siap tinjau, dan kata sandi awal. */
trait SiapkanPengajuan
{
    use DataPendaftaran;

    protected const SANDI_AWAL = 'AwalTes123';

    protected function siapkanK2(): void
    {
        $this->seedK1();
        config(['minect.initial_password' => self::SANDI_AWAL]);
    }

    protected function buatAdmin(string $email = 'admin.uji@example.test'): User
    {
        return User::firstOrCreate(['email' => $email], [
            'name' => 'Admin Uji', 'password' => 'rahasia123', 'user_group' => 'admin',
        ]);
    }

    /** Kirim formulir pendaftaran lewat rute publik, lalu kembalikan pengajuannya. */
    protected function ajukan(array $ubah = [], string $huruf = 'aaaa'): PengajuanPendaftaran
    {
        $this->post('/register', $this->payload($ubah, $huruf))->assertRedirect(route('register.selesai'));

        return PengajuanPendaftaran::where('email', $ubah['email'] ?? 'budi.baru@example.test')
            ->latest('id')->firstOrFail();
    }

    protected function level(string $kode): UmkmLevel
    {
        return UmkmLevel::where('kode', $kode)->firstOrFail();
    }

    protected function setujui(PengajuanPendaftaran $p, UmkmLevel $level, ?string $alasan = null, ?User $admin = null)
    {
        return $this->actingAs($admin ?? $this->buatAdmin('pemutus' . $p->id . '@example.test'))
            ->post(route('admin.pengajuan.setujui', $p->id), [
                'level_akhir_id' => $level->id,
                'alasan'         => $alasan,
            ]);
    }
}
