<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('umkm_id');
            $table->foreign('umkm_id')->references('id')->on('umkm')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('kode_akun');
            $table->string('nama_akun');
            $table->string('keterangan')->nullable();
            $table->decimal('debit',15,2)->default(0);
            $table->decimal('kredit',15,2)->default(0);
            $table->string('ref_tipe')->nullable(); // penjualan
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
