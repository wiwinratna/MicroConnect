<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')
                  ->constrained('umkm')
                  ->onDelete('cascade');

            $table->string('kode_bahan', 20)->unique();   // BB-00001
            $table->string('nama_bahan');
            $table->string('satuan', 20)->nullable();     // kg, pcs, liter
            $table->decimal('stok_awal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
