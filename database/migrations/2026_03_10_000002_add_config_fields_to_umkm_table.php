<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah field konfigurasi ke tabel umkm:
     * - jenis_usaha   : kategori usaha (kuliner, jasa, perdagangan, dll)
     * - no_whatsapp   : nomor WA pemilik (untuk notifikasi)
     * - recording_method : periodik | perpetual
     * - inventory_method : FIFO | LIFO | Average
     * - status        : aktif | nonaktif
     *
     * Semua field nullable / ada default supaya data lama tidak rusak.
     */
    public function up(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->string('jenis_usaha', 50)->nullable()->after('no_telepon');
            $table->string('no_whatsapp', 20)->nullable()->after('jenis_usaha');

            // Konfigurasi metode pencatatan & penilaian persediaan
            $table->enum('recording_method', ['periodik', 'perpetual'])
                  ->default('periodik')
                  ->after('no_whatsapp');

            $table->enum('inventory_method', ['FIFO', 'LIFO', 'Average'])
                  ->default('Average')
                  ->after('recording_method');

            // Status keaktifan UMKM
            $table->enum('status', ['aktif', 'nonaktif'])
                  ->default('aktif')
                  ->after('inventory_method');
        });
    }

    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_usaha',
                'no_whatsapp',
                'recording_method',
                'inventory_method',
                'status',
            ]);
        });
    }
};
