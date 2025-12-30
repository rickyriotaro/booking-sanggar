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
        Schema::table('stock_snapshots', function (Blueprint $table) {
            // Tambah kolom sisa_stok_tersedia jika belum ada
            if (!Schema::hasColumn('stock_snapshots', 'sisa_stok_tersedia')) {
                $table->integer('sisa_stok_tersedia')->default(0)->after('stok_from_orders');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_snapshots', function (Blueprint $table) {
            $table->dropColumn('sisa_stok_tersedia');
        });
    }
};
