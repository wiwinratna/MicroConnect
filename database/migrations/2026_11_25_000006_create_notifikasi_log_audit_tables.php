<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: notifikasi (calon UMKM belum punya baris umkm, sehingga tidak bisa
 * memakai notifikasi_log v1.0) dan log audit keputusan level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->nullable()->constrained('umkm')->nullOnDelete();
            $table->foreignId('pengajuan_id')->nullable()->constrained('pengajuan_pendaftaran')->nullOnDelete();
            $table->string('no_tujuan', 20);
            $table->enum('jenis', [
                'registrasi_diterima', 'akun_aktif', 'pengajuan_ditolak',
                'periode_dibuka', 'hasil_penilaian',
            ]);
            $table->enum('saluran', ['whatsapp', 'aplikasi']);
            $table->text('isi');
            $table->enum('status', ['antri', 'terkirim', 'gagal'])->default('antri');
            $table->unsignedTinyInteger('percobaan')->default(0);
            $table->timestamp('dikirim_pada')->nullable();
            $table->text('pesan_galat')->nullable();
            $table->timestamps();

            $table->index(['umkm_id', 'jenis']);
            $table->index('status');
        });

        Schema::create('log_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 100);
            $table->string('entitas', 100);
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->timestamp('waktu')->useCurrent();
            $table->timestamps();

            $table->index(['entitas', 'entitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit');
        Schema::dropIfExists('notifikasi');
    }
};
