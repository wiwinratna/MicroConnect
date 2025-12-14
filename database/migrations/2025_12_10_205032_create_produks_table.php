<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();

            // relasi ke UMKM
            $table->foreignId('umkm_id')
                  ->constrained('umkm')
                  ->onDelete('cascade');

            // identitas produk
            $table->string('kode_produk', 20)->unique();  // PRD-00001
            $table->string('nama_produk');
            $table->string('kategori')->nullable();

            // harga jual (manual)
            $table->decimal('harga_jual', 15, 2)->default(0);

            // status aktif / tidak
            $table->boolean('aktif')->default(true);

            // keterangan tambahan produk
            $table->text('keterangan')->nullable();

            // ===== TAMBAHAN =========
            // stok produk jadi
            $table->decimal('stok', 15, 3)->default(0);

            // harga pokok (HPP per unit)
            $table->decimal('harga_pokok', 15, 2)->default(0);

            // foto produk
            $table->string('foto_path')->nullable();
            // =========================

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
