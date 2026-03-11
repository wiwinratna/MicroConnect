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
        // Karena ada index global lama, kita perlu drop
        Schema::table('coa', function (Blueprint $table) {
            $table->dropUnique('coa_kode_akun_unique');
            $table->unique(['umkm_id', 'kode_akun'], 'coa_umkm_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coa', function (Blueprint $table) {
            $table->dropUnique('coa_umkm_kode_unique');
            $table->unique('kode_akun', 'coa_kode_akun_unique');
        });
    }
};
