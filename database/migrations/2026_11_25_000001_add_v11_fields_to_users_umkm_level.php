<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: kolom tambahan pada tabel v1.0 (users, umkm_level).
 * Semua kolom boleh null agar data lama tidak terdampak (BR-14).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('email');
            $table->string('no_wa', 20)->nullable()->unique()->after('username');
        });

        Schema::table('umkm_level', function (Blueprint $table) {
            $table->unsignedTinyInteger('urutan')->nullable()->after('nama_level');
            $table->unsignedTinyInteger('skor_min')->nullable()->after('urutan');
            $table->unsignedTinyInteger('skor_max')->nullable()->after('skor_min');
        });
    }

    public function down(): void
    {
        Schema::table('umkm_level', function (Blueprint $table) {
            $table->dropColumn(['urutan', 'skor_min', 'skor_max']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['no_wa']);
            $table->dropColumn(['username', 'no_wa']);
        });
    }
};
