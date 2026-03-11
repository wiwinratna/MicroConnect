<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notifikasi Log: audit trail setiap pengiriman notifikasi WA.
     * - Tipe: piutang_reminder_h3, piutang_reminder_h0, piutang_lunas, iuran_reminder
     * - Polymorphic: bisa merujuk ke piutang atau iuran_bulanan
     * - status: pending → terkirim / gagal
     */
    public function up(): void
    {
        Schema::create('notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();

            // Tipe notifikasi
            $table->string('tipe', 60);
            // e.g. 'piutang_reminder_h3' | 'piutang_reminder_h0' | 'piutang_lunas' | 'iuran_reminder'

            $table->string('tujuan', 20);   // nomor WA tujuan (pelanggan atau pemilik)
            $table->text('pesan');

            $table->enum('status', ['pending', 'terkirim', 'gagal'])->default('pending');
            $table->text('response')->nullable();   // response raw dari API (JSON string)

            // Polymorphic: bisa ke piutang atau iuran_bulanan
            // notifiable_type: "App\Models\Piutang" | "App\Models\IuranBulanan"
            $table->nullableMorphs('notifiable');

            $table->timestamp('dikirim_pada')->nullable();

            $table->timestamps();

            $table->index(['umkm_id', 'tipe']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_log');
    }
};
