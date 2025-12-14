<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();

            // sesuaikan kalau nama tabel UMKM kamu beda
            $table->foreignId('umkm_id')
                ->constrained('umkm')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->string('kode_penjualan')->unique();

            $table->string('pembeli', 100)->nullable();
            $table->text('catatan')->nullable();

            $table->decimal('total', 15, 2)->default(0);

            $table->timestamps();

            // optional index buat pencarian cepat
            $table->index(['umkm_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
