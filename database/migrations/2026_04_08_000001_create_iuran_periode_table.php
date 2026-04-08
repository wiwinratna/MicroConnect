<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master periode iuran.
     * Admin/KADIN membuat periode, lalu sistem generate tagihan ke semua UMKM aktif.
     */
    public function up(): void
    {
        Schema::create('iuran_periode', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();          // "2026-03"
            $table->decimal('nominal_default', 12, 2);        // nominal iuran default
            $table->date('jatuh_tempo');                       // batas akhir pembayaran
            $table->string('status', 20)->default('draft');    // draft | terbit | selesai
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iuran_periode');
    }
};
