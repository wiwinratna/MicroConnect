<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: instrumen kuesioner (Tabel 3.6). Skor ada di data (BR-09, BR-12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('definisi')->nullable();
            $table->text('pertanyaan');
            $table->enum('sumber_data', ['input_umkm', 'pilihan_jawaban'])->default('pilihan_jawaban');
            $table->unsignedTinyInteger('urutan');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pilihan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->string('teks', 255);
            $table->text('contoh')->nullable();
            $table->decimal('nilai_min', 18, 2)->nullable(); // dokumentasi saja
            $table->decimal('nilai_max', 18, 2)->nullable(); // dokumentasi saja
            $table->unsignedTinyInteger('skor');
            $table->boolean('di_luar_batas_mikro')->default(false);
            $table->unsignedTinyInteger('urutan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilihan_jawaban');
        Schema::dropIfExists('indikator');
    }
};
