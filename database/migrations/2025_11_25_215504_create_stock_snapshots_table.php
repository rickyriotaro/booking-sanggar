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
        Schema::create('stock_snapshots', function (Blueprint $table) {
            $table->id();
            
            // Service type (kostum, tari, rias)
            $table->string('service_type');
            
            // Reference to the actual service
            $table->unsignedBigInteger('service_id');
            $table->string('service_name');
            
            // Original stock/slot value (as set by admin)
            $table->integer('original_stock');
            
            // Current booked quantity (from order_details)
            $table->integer('booked_quantity')->default(0);
            
            // Available for new orders = original - booked
            $table->integer('available_quantity')->default(0);
            
            // Last update info
            $table->unsignedBigInteger('updated_by_admin')->nullable();
            $table->string('update_reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            
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
        Schema::dropIfExists('stock_snapshots');
    }
};
