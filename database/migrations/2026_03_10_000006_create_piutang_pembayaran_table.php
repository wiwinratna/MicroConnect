<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Piutang Pembayaran: riwayat cicilan/pembayaran per tagihan piutang.
     * - Satu piutang bisa punya banyak record pembayaran (cicilan)
     * - Setiap simpan pembayaran, update field sudah_dibayar & sisa di tabel piutang
     */
    public function up(): void
    {
        Schema::create('piutang_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piutang_id')->constrained('piutang')->cascadeOnDelete();

            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('metode_bayar', 50)->nullable();  // tunai, transfer, qris
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('piutang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutang_pembayaran');
    }
};
