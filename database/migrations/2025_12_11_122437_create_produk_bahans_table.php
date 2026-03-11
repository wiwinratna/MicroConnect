<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produk_bahan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produk_id')
                  ->constrained('produk')
                  ->onDelete('cascade');

            $table->foreignId('bahan_id')
                  ->constrained('bahan_baku')
                  ->onDelete('restrict');

            // qty bahan untuk menghasilkan 1 unit produk
            $table->decimal('qty', 15, 3);
            $table->string('satuan', 50)->nullable(); // contoh: gram, ml, pcs

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_bahan');
    }
};
