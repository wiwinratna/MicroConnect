<?php

namespace Tests\Concerns;

use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;

/** Pembantu tes K3: UMKM dengan rumpun/flag tertentu dan permintaan dengan model pengguna segar. */
trait SiapkanModul
{
    use DataPendaftaran;

    private int $nomorUmkm = 0;

    protected function siapkanK3(): void
    {
        $this->seedK1();
    }

    protected function levelKode(string $kode): UmkmLevel
    {
        return UmkmLevel::where('kode', $kode)->firstOrFail();
    }

    /** UMKM aktif; $data menimpa kolom umkm (rumpun, memiliki_persediaan, melakukan_produksi). */
    protected function buatUmkm(array $data = [], string $kodeLevel = 'L2'): User
    {
        $n = ++$this->nomorUmkm;

        $user = User::create([
            'name' => "Pemilik {$n}", 'email' => "umkm{$n}@example.test",
            'password' => 'rahasia123', 'user_group' => 'pelakuusaha',
            'must_change_password' => false,
        ]);

        Umkm::create(array_merge([
            'kode_umkm' => sprintf('UM%05d', 900 + $n), 'user_id' => $user->id,
            'level_id'  => $this->levelKode($kodeLevel)->id,
            'status'    => 'aktif',
        ], $data));

        return $user;
    }

    protected function buatAdminK3(): User
    {
        return User::firstOrCreate(['email' => 'admin.k3@example.test'], [
            'name' => 'Admin K3', 'password' => 'rahasia123', 'user_group' => 'admin',
        ]);
    }

    /** Selalu pakai model pengguna segar agar relasi umkm/level tidak basi antar permintaan. */
    protected function sebagai(User $user)
    {
        return $this->actingAs(User::findOrFail($user->id));
    }

    protected function ke(User $user, string $url)
    {
        return $this->sebagai($user)->get($url);
    }

    /** Modul tersedia = halaman terbuka (bukan dialihkan ke dashboard). */
    protected function assertTersedia(User $user, string $url): void
    {
        $this->ke($user, $url)->assertOk();
    }

    /** Modul tidak tersedia = dialihkan ke dashboard dengan pesan. */
    protected function assertDiblokir(User $user, string $url): void
    {
        $this->ke($user, $url)->assertRedirect(route('umkm.dashboard'))->assertSessionHas('error');
    }
}
