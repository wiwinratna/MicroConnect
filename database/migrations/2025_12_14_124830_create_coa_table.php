<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->id();

            // contoh: Aset, Kewajiban, Modal, Pendapatan, Beban
            $table->string('header_akun', 50);

            // contoh: 111, 112, 400, 500
            $table->string('kode_akun', 10)->unique();

            // contoh: Kas, Persediaan, Pendapatan Penjualan
            $table->string('nama_akun', 100);

            // Debit / Kredit
            $table->enum('posisi_dr_cr', ['Debit', 'Kredit']);

            // kalau sistem multi UMKM
            $table->foreignId('umkm_id')
                  ->nullable()
                  ->constrained('umkm')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
