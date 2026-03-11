<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Iuran bulanan: tagihan aplikasi per UMKM per periode.
     * - Semua level dikenakan nominal yang sama (diisi dari UmkmLevel.iuran_bulanan)
     * - Status: belum_bayar → lunas / terlambat
     * - Midtrans: simpan order_id dan payment_url setelah create payment
     */
    public function up(): void
    {
        Schema::create('iuran_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();

            $table->string('periode', 7);                        // "2026-03"
            $table->decimal('nominal', 12, 2)->default(0);       // dari iuran_bulanan level saat generate
            $table->enum('status', ['belum_bayar', 'lunas', 'terlambat'])->default('belum_bayar');
            $table->date('jatuh_tempo')->nullable();             // biasanya akhir bulan periode

            // Midtrans fields (nullable — diisi saat create payment request)
            $table->string('midtrans_order_id', 100)->nullable()->unique();
            $table->string('midtrans_payment_url', 1000)->nullable();
            $table->timestamp('dibayar_pada')->nullable();

            $table->timestamps();

            // Satu UMKM hanya boleh punya 1 record iuran per periode
            $table->unique(['umkm_id', 'periode']);
            $table->index(['umkm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iuran_bulanan');
    }
};
