<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('produksi_detail', function (Blueprint $table) {
        if (!Schema::hasColumn('produksi_detail', 'qty_hasil')) {
            $table->decimal('qty_hasil', 15, 3)->default(0)->after('produk_id');
        }

        if (!Schema::hasColumn('produksi_detail', 'hpp_per_unit')) {
            $table->decimal('hpp_per_unit', 15, 2)->default(0)->after('qty_hasil');
        }

        if (!Schema::hasColumn('produksi_detail', 'hpp_total')) {
            $table->decimal('hpp_total', 15, 2)->default(0)->after('hpp_per_unit');
        }
    });
}

public function down(): void
{
    Schema::table('produksi_detail', function (Blueprint $table) {
        if (Schema::hasColumn('produksi_detail', 'hpp_total')) $table->dropColumn('hpp_total');
        if (Schema::hasColumn('produksi_detail', 'hpp_per_unit')) $table->dropColumn('hpp_per_unit');
        if (Schema::hasColumn('produksi_detail', 'qty_hasil')) $table->dropColumn('qty_hasil');
    });
}

};
