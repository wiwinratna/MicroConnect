<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id();

            // FK ke header penjualan
            $table->foreignId('penjualan_id')
                ->constrained('penjualan')
                ->cascadeOnDelete();

            // FK ke produk
            // kalau nama tabel produk kamu bukan 'produk', ganti sesuai tabel aslimu
            $table->foreignId('produk_id')
                ->constrained('produk')
                ->cascadeOnDelete();

            $table->decimal('qty', 15, 3);
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->timestamps();

            // optional index
            $table->index(['penjualan_id']);
            $table->index(['produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
