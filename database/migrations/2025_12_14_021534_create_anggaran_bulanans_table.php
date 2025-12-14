<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anggaran_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();

            $table->string('periode', 7); // "2025-12"
            $table->decimal('target_unit', 15, 2)->default(1); // saran: default 1
            $table->decimal('total', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['umkm_id', 'periode']);
        });

        Schema::create('anggaran_bulanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran_bulanan')->cascadeOnDelete();

            $table->string('nama_biaya', 100);
            $table->decimal('nominal', 15, 2)->default(0);

            $table->timestamps();

            $table->index('anggaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran_bulanan_item');
        Schema::dropIfExists('anggaran_bulanan');
    }
};
