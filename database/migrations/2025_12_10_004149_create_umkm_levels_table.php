<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkm_level', function (Blueprint $table) {
            $table->id();

            $table->string('kode', 10);        // L1, L2, L3
            $table->string('nama_level');      // Level 1 - Basic, dst
            $table->text('deskripsi')->nullable();

            // daftar fitur dalam bentuk JSON
            // contoh isi nanti: ["pemasukan", "pengeluaran", "produk", "stok"]
            $table->json('fitur')->nullable();

            // iuran bulanan per level (optional: 0 kalau gratis)
            $table->decimal('iuran_bulanan', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm_level');
    }
};
