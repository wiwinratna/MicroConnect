<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pelanggan: data pelanggan per UMKM.
     * - Digunakan sebagai referensi untuk pencatatan piutang
     * - no_whatsapp dipakai untuk kirim notifikasi reminder pembayaran
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();

            $table->string('nama_pelanggan');
            $table->string('no_whatsapp', 20)->nullable();
            $table->string('alamat')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('umkm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
