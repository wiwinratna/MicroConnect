<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom histori di piutang_pembayaran untuk mencatat:
     * - saldo_sebelum: sisa piutang sebelum pembayaran ini
     * - saldo_sesudah: sisa piutang sesudah pembayaran ini
     * - jatuh_tempo_baru: jatuh tempo baru (wajib jika parsial)
     * - is_pelunasan: flag apakah ini pelunasan
     */
    public function up(): void
    {
        Schema::table('piutang_pembayaran', function (Blueprint $table) {
            $table->decimal('saldo_sebelum', 15, 2)->nullable()->after('jumlah_bayar');
            $table->decimal('saldo_sesudah', 15, 2)->nullable()->after('saldo_sebelum');
            $table->date('jatuh_tempo_baru')->nullable()->after('saldo_sesudah');
            $table->boolean('is_pelunasan')->default(false)->after('jatuh_tempo_baru');
        });
    }

    public function down(): void
    {
        Schema::table('piutang_pembayaran', function (Blueprint $table) {
            $table->dropColumn(['saldo_sebelum', 'saldo_sesudah', 'jatuh_tempo_baru', 'is_pelunasan']);
        });
    }
};
