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
        // Check if order_detail_id column already exists
        if (Schema::hasColumn('reviews', 'order_detail_id')) {
            return; // Column already exists, skip
        }

        Schema::table('reviews', function (Blueprint $table) {
            // Add order_detail_id column as nullable first
            $table->foreignId('order_detail_id')
                ->after('order_id')
                ->nullable()
                ->constrained('order_details')
                ->onDelete('cascade');
        });

        // Populate order_detail_id for existing reviews
        // Get first order_detail for each order (if exists)
        DB::statement('
            UPDATE reviews r
            SET r.order_detail_id = (
                SELECT MIN(od.id) FROM order_details od 
                WHERE od.order_id = r.order_id
                LIMIT 1
            )
            WHERE r.order_detail_id IS NULL
        ');

        // Now make the column non-nullable and add unique constraint
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_detail_id')->nullable(false)->change();
            $table->unique(['order_detail_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Only drop if they exist
            try {
                $table->dropUnique(['order_detail_id', 'user_id']);
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            try {
                $table->dropForeignKey(['order_detail_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            if (Schema::hasColumn('reviews', 'order_detail_id')) {
                $table->dropColumn('order_detail_id');
            }
        });
    }
};
