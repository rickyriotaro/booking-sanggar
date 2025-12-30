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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            
            // Service reference
            $table->string('service_type');
            $table->unsignedBigInteger('service_id');
            $table->string('service_name');
            
            // Stock change details
            $table->integer('old_stock');
            $table->integer('new_stock');
            $table->integer('change_quantity');
            $table->string('change_type'); // 'admin_add', 'admin_reduce', 'order_booked', 'order_returned'
            
            // Related records
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            
            // Reason for change
            $table->string('reason')->nullable();
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Index for quick lookup
            $table->index(['service_type', 'service_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
