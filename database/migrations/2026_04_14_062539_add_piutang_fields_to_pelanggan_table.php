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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('nama_instansi', 100)->nullable()->after('nama_pelanggan');
            $table->string('no_ktp', 50)->nullable()->after('nama_instansi');
            $table->string('kontak_alternatif', 20)->nullable()->after('no_whatsapp');
            $table->string('nama_pic', 100)->nullable()->after('alamat');
            $table->decimal('batas_maksimal_piutang', 15, 2)->nullable()->after('catatan');
            $table->integer('jatuh_tempo_default')->nullable()->after('batas_maksimal_piutang');
            $table->text('catatan_penagihan')->nullable()->after('jatuh_tempo_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn([
                'nama_instansi',
                'no_ktp',
                'kontak_alternatif',
                'nama_pic',
                'batas_maksimal_piutang',
                'jatuh_tempo_default',
                'catatan_penagihan'
            ]);
        });
    }
};
