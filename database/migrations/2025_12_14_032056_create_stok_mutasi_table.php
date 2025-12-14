<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();

            // salah satu terisi: bahan_id atau produk_id
            $table->foreignId('bahan_id')->nullable()->constrained('bahan_baku')->nullOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('produk')->nullOnDelete();

            $table->date('tanggal');
            $table->enum('jenis', ['MASUK', 'KELUAR']);
            $table->decimal('qty', 15, 3)->default(0);

            // optional: untuk simpan harga unit (buat HPP)
            $table->decimal('harga_unit', 15, 2)->nullable();

            // referensi transaksi
            $table->string('ref_tipe', 30); // pembelian / produksi / penjualan
            $table->unsignedBigInteger('ref_id'); // id header
            $table->unsignedBigInteger('ref_detail_id')->nullable(); // id detail (opsional)

            $table->timestamps();

            $table->index(['umkm_id', 'bahan_id']);
            $table->index(['umkm_id', 'produk_id']);
            $table->index(['ref_tipe', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
