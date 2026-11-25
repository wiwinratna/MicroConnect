<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: data calon UMKM sebelum akun ada (BR-06, BR-13). Tanpa kata sandi.
 *
 * Constraint "satu pengajuan menunggu per no_wa / username / email": MySQL tidak
 * punya unique parsial, jadi dipakai kolom virtual yang bernilai hanya saat
 * status 'menunggu', lalu diberi indeks unik (NULL boleh berulang).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->nullable()->constrained('umkm')->nullOnDelete();

            $table->string('nama_pemilik', 150);
            $table->string('email', 150);
            $table->string('username', 50);
            $table->string('no_wa', 20);

            $table->string('nama_usaha', 150);
            $table->text('alamat');
            $table->string('no_telepon', 20)->nullable();

            $table->foreignId('jenis_usaha_id')->constrained('jenis_usaha');
            $table->enum('rumpun', ['barang', 'jasa']);
            $table->boolean('memiliki_persediaan');
            $table->boolean('melakukan_produksi');

            $table->boolean('persetujuan_data_pribadi');
            $table->timestamp('tanggal_persetujuan')->nullable();

            $table->boolean('perlu_keputusan_admin')->default(false);
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('alasan_penolakan')->nullable();

            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->string('no_wa_menunggu', 20)->nullable()
                  ->virtualAs("CASE WHEN status = 'menunggu' THEN no_wa ELSE NULL END")->unique();
            $table->string('username_menunggu', 50)->nullable()
                  ->virtualAs("CASE WHEN status = 'menunggu' THEN username ELSE NULL END")->unique();
            $table->string('email_menunggu', 150)->nullable()
                  ->virtualAs("CASE WHEN status = 'menunggu' THEN email ELSE NULL END")->unique();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pendaftaran');
    }
};
