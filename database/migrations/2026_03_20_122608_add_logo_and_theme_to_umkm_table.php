<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->string('logo_path', 255)->nullable()->after('status');
            $table->string('warna_tema', 7)->nullable()->default('#0d6efd')->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'warna_tema']);
        });
    }
};
