<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Umkm;
use App\Models\UmkmLevel;

/**
 * FixAhmadSeeder
 * Melengkapi akun UMKM Ahmad yang gagal pada seeder sebelumnya.
 */
class FixAhmadSeeder extends Seeder
{
    public function run(): void
    {
        $levelL3 = UmkmLevel::where('kode', 'L3')->first();

        $user = User::where('email', 'ahmad@minnect.id')->first();

        if (! $user) {
            $user = User::create([
                'name'                => 'Ahmad Fauzi',
                'email'               => 'ahmad@minnect.id',
                'password'            => Hash::make('Umkm@9012'),
                'user_group'          => 'pelakuusaha',
                'must_change_password' => false,
            ]);
        }

        if (! $user->umkm()->exists()) {
            // Kode berikutnya setelah UM00005 (berdasarkan kondisi DB saat ini)
            $kode = 'UM00006';
            $umkm = $user->umkm()->create([
                'kode_umkm'        => $kode,
                'nama_usaha'       => 'Bengkel Las Fauzi',
                'nib'              => '1234567890003',
                'alamat'           => 'Jl. Industri No. 8, Surabaya',
                'no_telepon'       => '08111000003',
                'no_whatsapp'      => '08111000003',
                'jenis_usaha'      => 'Manufaktur',
                'recording_method' => 'perpetual',
                'inventory_method' => 'Average',
                'status'           => 'aktif',
                'level_id'         => $levelL3?->id,
            ]);
            $umkm->seedDefaultCoa();
            $this->command->info("UMKM Ahmad dibuat: {$kode}");
        } else {
            $this->command->info('UMKM Ahmad sudah ada, skip.');
        }
    }
}
