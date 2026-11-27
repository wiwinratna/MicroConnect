<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Login memakai email saja, jadi pendaftaran tidak lagi meminta username. Kolomnya
 * dipertahankan agar data lama utuh, tetapi boleh kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_pendaftaran', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Kolom wajib butuh isi: baris tanpa username diberi nama pengganti yang unik.
        DB::table('pengajuan_pendaftaran')->whereNull('username')
            ->update(['username' => DB::raw("CONCAT('pengajuan_', id)")]);

        Schema::table('pengajuan_pendaftaran', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->change();
        });
    }
};
