<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Piutang: catatan tagihan pelanggan yang belum lunas.
     * - Terhubung ke pelanggan (data kontak)
     * - Opsional: terhubung ke penjualan (kalau transaksi berasal dari penjualan)
     * - reminder_h3 & reminder_h0: flag apakah sudah dikirim notif WA
     */
    public function up(): void
    {
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();

            // Opsional: hubungkan ke transaksi penjualan
            $table->foreignId('penjualan_id')
                  ->nullable()
                  ->constrained('penjualan')
                  ->nullOnDelete();

            $table->string('kode_piutang', 20)->unique();  // PT-00001, PT-00002

            $table->date('tanggal');
            $table->date('jatuh_tempo');

            $table->decimal('nominal_awal', 15, 2);            // total hutang awal
            $table->decimal('sudah_dibayar', 15, 2)->default(0);
            // sisa dihitung virtual: nominal_awal - sudah_dibayar
            // disimpan juga untuk kemudahan query filter
            $table->decimal('sisa', 15, 2)->default(0);

            $table->enum('status', ['belum_lunas', 'sebagian', 'lunas'])->default('belum_lunas');

            $table->text('catatan')->nullable();

            // Flag reminder WhatsApp (agar tidak kirim duplikat)
            $table->boolean('reminder_h3_terkirim')->default(false);
            $table->boolean('reminder_h0_terkirim')->default(false);

            $table->timestamps();

            $table->index(['umkm_id', 'status']);
            $table->index('jatuh_tempo');               // untuk query scheduler
            $table->index('pelanggan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};
