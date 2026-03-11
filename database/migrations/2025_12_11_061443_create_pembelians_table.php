<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
        $table->id();
        $table->foreignId('umkm_id')
            ->constrained('umkm')
            ->onDelete('cascade');

        $table->string('kode_pembelian', 20)->unique(); // PB-00001 dst
        $table->string('nomor_nota')->nullable();       // nomor struk vendor
        $table->date('tanggal');
        $table->string('supplier')->nullable();
        $table->text('catatan')->nullable();

        $table->decimal('total', 15, 2)->default(0);
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};

