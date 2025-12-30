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
        // Add views_count to costumes table
        Schema::table('costumes', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->comment('Jumlah orang yang melihat produk ini');
        });

        // Add views_count to dance_services table
        Schema::table('dance_services', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->comment('Jumlah orang yang melihat paket ini');
        });

        // Add views_count to makeup_services table
        Schema::table('makeup_services', function (Blueprint $table) {
            $table->unsignedInteger('views_count')->default(0)->comment('Jumlah orang yang melihat paket ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costumes', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });

        Schema::table('dance_services', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });

        Schema::table('makeup_services', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });
    }
};
