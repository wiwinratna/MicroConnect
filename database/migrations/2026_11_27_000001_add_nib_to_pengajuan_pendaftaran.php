<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** NIB opsional pada pendaftaran mandiri; disalin ke umkm.nib saat pengajuan disetujui. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_pendaftaran', function (Blueprint $table) {
            $table->string('nib', 13)->nullable()->after('nama_usaha');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_pendaftaran', function (Blueprint $table) {
            $table->dropColumn('nib');
        });
    }
};
