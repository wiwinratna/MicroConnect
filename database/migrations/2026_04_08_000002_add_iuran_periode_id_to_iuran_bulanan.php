<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan:
     * 1. iuran_periode_id FK (nullable) ke iuran_bulanan
     * 2. midtrans_snap_token untuk integrasi SNAP popup
     * 3. Mengubah status dari ENUM ke VARCHAR agar lebih fleksibel
     *    (status baru: belum_bayar, pending, lunas, expire, cancel, deny)
     *
     * Data lama tetap aman — iuran_periode_id nullable, tidak wajib diisi.
     */
    public function up(): void
    {
        // Ubah status dari ENUM ke VARCHAR(20) agar bisa menampung status Midtrans
        DB::statement("ALTER TABLE iuran_bulanan MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'belum_bayar'");

        Schema::table('iuran_bulanan', function (Blueprint $table) {
            // FK ke master periode (nullable untuk data lama)
            $table->foreignId('iuran_periode_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('iuran_periode')
                  ->nullOnDelete();

            // Midtrans SNAP token (dipakai untuk popup)
            $table->string('midtrans_snap_token', 500)
                  ->nullable()
                  ->after('midtrans_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('iuran_bulanan', function (Blueprint $table) {
            $table->dropForeign(['iuran_periode_id']);
            $table->dropColumn(['iuran_periode_id', 'midtrans_snap_token']);
        });

        // Kembalikan ke ENUM jika rollback
        DB::statement("ALTER TABLE iuran_bulanan MODIFY COLUMN status ENUM('belum_bayar','lunas','terlambat') NOT NULL DEFAULT 'belum_bayar'");
    }
};
