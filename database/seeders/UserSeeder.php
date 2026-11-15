<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Umkm;
use App\Models\UmkmLevel;

/**
 * UserSeeder
 *
 * Membuat akun admin + beberapa akun UMKM beserta data profil usahanya.
 *
 * Jalankan: php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ================================================================
        // 1. AKUN ADMIN
        // ================================================================
        $admins = [
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@minnect.id',
                'password' => 'Admin@1234',
            ],
            [
                'name'     => 'Admin Kadin',
                'email'    => 'kadin@minnect.id',
                'password' => 'Kadin@1234',
            ],
        ];

        foreach ($admins as $adminData) {
            User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name'                => $adminData['name'],
                    'password'            => Hash::make($adminData['password']),
                    'user_group'          => 'admin',
                    'must_change_password' => false,
                ]
            );
        }

        // ================================================================
        // 2. AKUN UMKM
        // ================================================================
        $levelL1 = UmkmLevel::where('kode', 'L1')->first();
        $levelL2 = UmkmLevel::where('kode', 'L2')->first();
        $levelL3 = UmkmLevel::where('kode', 'L3')->first();

        $umkmData = [
            [
                'user' => [
                    'name'     => 'Budi Santoso',
                    'email'    => 'budi@minnect.id',
                    'password' => 'Umkm@1234',
                ],
                'umkm' => [
                    'nama_usaha'       => 'Warung Makan Bu Budi',
                    'nib'              => '1234567890001',
                    'alamat'           => 'Jl. Merdeka No. 1, Bandung',
                    'no_telepon'       => '08111000001',
                    'no_whatsapp'      => '08111000001',
                    'jenis_usaha'      => 'Kuliner',
                    'recording_method' => 'periodik',
                    'inventory_method' => 'Average',
                    'status'           => 'aktif',
                    'level_id'         => $levelL1?->id,
                ],
            ],
            [
                'user' => [
                    'name'     => 'Siti Rahayu',
                    'email'    => 'siti@minnect.id',
                    'password' => 'Umkm@5678',
                ],
                'umkm' => [
                    'nama_usaha'       => 'Toko Batik Siti',
                    'nib'              => '1234567890002',
                    'alamat'           => 'Jl. Sudirman No. 12, Yogyakarta',
                    'no_telepon'       => '08111000002',
                    'no_whatsapp'      => '08111000002',
                    'jenis_usaha'      => 'Fashion & Tekstil',
                    'recording_method' => 'periodik',
                    'inventory_method' => 'Average',
                    'status'           => 'aktif',
                    'level_id'         => $levelL2?->id,
                ],
            ],
            [
                'user' => [
                    'name'     => 'Ahmad Fauzi',
                    'email'    => 'ahmad@minnect.id',
                    'password' => 'Umkm@9012',
                ],
                'umkm' => [
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
                ],
            ],
        ];

        foreach ($umkmData as $data) {
            // Buat / ambil user
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name'                => $data['user']['name'],
                    'password'            => Hash::make($data['user']['password']),
                    'user_group'          => 'pelakuusaha',
                    'must_change_password' => false,
                ]
            );

            // Buat profil UMKM bila belum ada
            if (! $user->umkm()->exists()) {
                $umkm = $user->umkm()->create(array_merge(
                    $data['umkm'],
                    ['kode_umkm' => Umkm::getKodeUmkm()]
                ));

                // Seed COA default
                $umkm->seedDefaultCoa();
            }
        }

        // ================================================================
        // OUTPUT ke konsol
        // ================================================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  AKUN BERHASIL DIBUAT');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('[ ADMIN ]');
        $this->command->info('  Email   : admin@minnect.id');
        $this->command->info('  Password: Admin@1234');
        $this->command->info('  ----');
        $this->command->info('  Email   : kadin@minnect.id');
        $this->command->info('  Password: Kadin@1234');
        $this->command->info('');
        $this->command->info('[ UMKM ]');
        $this->command->info('  Email   : budi@minnect.id');
        $this->command->info('  Password: Umkm@1234  (Level 1 - Dasar)');
        $this->command->info('  ----');
        $this->command->info('  Email   : siti@minnect.id');
        $this->command->info('  Password: Umkm@5678  (Level 2 - Menengah)');
        $this->command->info('  ----');
        $this->command->info('  Email   : ahmad@minnect.id');
        $this->command->info('  Password: Umkm@9012  (Level 3 - Lanjut)');
        $this->command->info('');
        $this->command->info('========================================');
    }
}
