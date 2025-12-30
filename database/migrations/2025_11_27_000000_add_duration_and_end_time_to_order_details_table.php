<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add support for Jasa Tari duration-based booking:
     * - service_duration: Duration in minutes (e.g., 30, 60, 120) - set by admin
     * - return_time: End time (HH:MM format) - auto-calculated from rental_time + duration
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('service_duration')->nullable()->comment('Durasi layanan dalam menit (untuk Jasa Tari)');
            $table->string('return_time')->nullable()->comment('Jam pengembalian (HH:MM) - auto-calculated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['service_duration', 'return_time']);
        });
    }
};
