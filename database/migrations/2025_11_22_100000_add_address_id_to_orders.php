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
        Schema::table('orders', function (Blueprint $table) {
            // Check if column exists, jika tidak tambah
            if (!Schema::hasColumn('orders', 'address_id')) {
                $table->unsignedBigInteger('address_id')->nullable()->after('user_id');
                $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
            }
            
            // Add payment_method jika belum ada
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('snap_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn(['address_id', 'payment_method']);
        });
    }
};
