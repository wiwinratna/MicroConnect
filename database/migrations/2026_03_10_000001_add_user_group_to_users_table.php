<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom user_group ke tabel users.
     * Kolom ini sudah dipakai di User model (fillable) dan AuthController
     * tapi belum ada di migration awal.
     * Nilai: 'admin' | 'pelakuusaha'
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Letakkan setelah password supaya urutan kolom enak dibaca
            $table->string('user_group', 20)->default('pelakuusaha')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_group');
        });
    }
};
