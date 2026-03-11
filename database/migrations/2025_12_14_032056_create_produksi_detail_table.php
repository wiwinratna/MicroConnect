<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produksi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();

            $table->decimal('qty_produksi', 15, 3)->default(0);

            $table->timestamps();

            $table->index(['produksi_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_detail');
    }
};
