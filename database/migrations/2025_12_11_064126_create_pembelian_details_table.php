<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();

            // Relasi ke pembelians
            $table->foreignId('pembelian_id')
                  ->constrained('pembelian')
                  ->onDelete('cascade');

            // Relasi bahan yang dibeli
            $table->foreignId('bahan_id')
                  ->constrained('bahan_baku')
                  ->onDelete('restrict');

            // qty & harga
            $table->decimal('qty', 15, 3);
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail');
    }
};
