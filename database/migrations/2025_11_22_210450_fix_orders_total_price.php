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
        // Update semua orders yang total_price = 0 atau NULL menjadi total_amount
        \Illuminate\Support\Facades\DB::statement('UPDATE orders SET total_price = total_amount WHERE total_price = 0 OR total_price IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan ke 0 untuk orders yang sebelumnya NULL/0
        // Tidak perlu rollback karena ini adalah fix data
    }
};
