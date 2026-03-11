<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkm', function (Blueprint $table) {
            $table->id();

            //  UMKM (UM00001, UM00002, ...)
            $table->string('kode_umkm', 10)->unique();

            // relasi ke users (FK ke tabel users)
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // relasi ke umkm_level (boleh null dulu)
            $table->foreignId('level_id')
                  ->nullable()
                  ->constrained('umkm_level')  // ← tabelnya jamak
                  ->nullOnDelete();

            // data profil UMKM (sementara boleh nullable semua)
            $table->string('nama_usaha')->nullable();
            $table->string('nib')->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm');
    }
};
