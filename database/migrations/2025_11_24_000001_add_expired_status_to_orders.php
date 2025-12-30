<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Laravel doesn't support altering ENUM directly, use raw SQL
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'settlement', 'success', 'expired', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
