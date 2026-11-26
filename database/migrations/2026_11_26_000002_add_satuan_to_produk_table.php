<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satuan produk (pcs, botol, jasa, dst.). Formulir v1.0 sudah memintanya, tetapi
 * kolomnya belum ada. Boleh null agar produk lama tetap utuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->string('satuan', 50)->nullable()->after('kategori');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
