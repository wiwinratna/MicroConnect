<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.1: jenis usaha (Tabel 3.7), modul, aturan modul per rumpun (Tabel 3.8),
 * serta kolom baru pada umkm. Kolom baru umkm boleh null (BR-14).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_usaha', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150)->unique();
            $table->enum('rumpun_bawaan', ['barang', 'jasa', 'ditentukan_umkm']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('modul', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('aturan_modul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('modul')->cascadeOnDelete();
            $table->enum('rumpun', ['barang', 'jasa']);
            $table->enum('kondisi', [
                'selalu',
                'jika_memiliki_persediaan',
                'jika_melakukan_produksi',
                'tidak_ditampilkan',
                'sesuai_level',
            ]);
            $table->string('label_tampil', 150)->nullable();
            $table->timestamps();

            $table->unique(['modul_id', 'rumpun']);
        });

        Schema::table('umkm', function (Blueprint $table) {
            $table->foreignId('jenis_usaha_id')->nullable()->after('jenis_usaha')
                  ->constrained('jenis_usaha')->nullOnDelete();
            $table->enum('rumpun', ['barang', 'jasa'])->nullable()->after('jenis_usaha_id');
            $table->boolean('memiliki_persediaan')->nullable()->after('rumpun');
            $table->boolean('melakukan_produksi')->nullable()->after('memiliki_persediaan');
            $table->boolean('persetujuan_data_pribadi')->default(false)->after('melakukan_produksi');
            $table->timestamp('tanggal_persetujuan')->nullable()->after('persetujuan_data_pribadi');
            $table->date('tanggal_aktivasi')->nullable()->after('tanggal_persetujuan');
        });
    }

    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_usaha_id');
            $table->dropColumn([
                'rumpun', 'memiliki_persediaan', 'melakukan_produksi',
                'persetujuan_data_pribadi', 'tanggal_persetujuan', 'tanggal_aktivasi',
            ]);
        });

        Schema::dropIfExists('aturan_modul');
        Schema::dropIfExists('modul');
        Schema::dropIfExists('jenis_usaha');
    }
};
