<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: periode, penilaian level (awal/ulang) dan detail jawaban (snapshot).
 * Riwayat tidak dihapus saat level berubah (BR-04).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['dibuka', 'ditutup'])->default('dibuka');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('penilaian_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->nullable()->constrained('umkm')->nullOnDelete();
            $table->foreignId('pengajuan_id')->nullable()->constrained('pengajuan_pendaftaran')->nullOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('periode_penilaian')->nullOnDelete();

            $table->enum('jenis', ['awal', 'ulang']);
            $table->date('tanggal_penilaian');
            $table->unsignedTinyInteger('total_skor');

            $table->foreignId('level_rekomendasi_id')->constrained('umkm_level');
            $table->enum('arah_rekomendasi', ['baru', 'naik', 'tetap', 'turun']);
            $table->foreignId('level_akhir_id')->nullable()->constrained('umkm_level');
            $table->text('alasan_penetapan')->nullable();

            $table->enum('status_peninjauan', ['menunggu', 'selesai'])->nullable();
            $table->text('catatan_peninjauan')->nullable();
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_peninjauan')->nullable();
            $table->text('tindak_lanjut_pembinaan')->nullable();

            $table->timestamps();

            // satu penilaian ulang per UMKM per periode (NULL boleh berulang untuk jenis awal)
            $table->unique(['umkm_id', 'periode_id']);
        });

        Schema::create('detail_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian_level')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator');
            $table->foreignId('pilihan_id')->constrained('pilihan_jawaban');
            $table->text('teks_pilihan');           // salinan teks saat dijawab
            $table->unsignedTinyInteger('skor');    // salinan skor saat dijawab
            $table->timestamps();

            $table->unique(['penilaian_id', 'indikator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penilaian');
        Schema::dropIfExists('penilaian_level');
        Schema::dropIfExists('periode_penilaian');
    }
};
