<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::factory()->create([
            //'name' => 'Test User',
            //'email' => 'test@example.com',
        //]);
        $this->call(UmkmLevelSeeder::class);
        $this->call(UserSeeder::class);

        // v1.1: data awal kuesioner, jenis usaha, dan aturan modul
        $this->call(JenisUsahaSeeder::class);
        $this->call(ModulSeeder::class);
        $this->call(IndikatorSeeder::class);

    }
}
