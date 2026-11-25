<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1 (K4b): rekam angka dari pembukuan sistem saat penilaian ulang dikirim (omzet 12 bulan,
 * modal, jumlah hari data, dan pilihan yang disarankan), agar Admin bisa melihat apakah UMKM
 * mengubah pilihan dari angka sistem. Skor tetap dari pilihan yang dipilih UMKM (BR-12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_level', function (Blueprint $table) {
            $table->json('data_sistem')->nullable()->after('tindak_lanjut_pembinaan');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian_level', function (Blueprint $table) {
            $table->dropColumn('data_sistem');
        });
    }
};
