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
        // Update dance_services table - add stock column for inventory
        if (!Schema::hasColumn('dance_services', 'stock')) {
            Schema::table('dance_services', function (Blueprint $table) {
                $table->integer('stock')->default(1)->comment('Number of available slots per date');
            });
        }

        // Update makeup_services table - already has is_available, ensure it exists
        if (!Schema::hasColumn('makeup_services', 'is_available')) {
            Schema::table('makeup_services', function (Blueprint $table) {
                $table->boolean('is_available')->default(true);
            });
        }

        // Create availability_calendar table to track service availability by date
        // This helps with quick querying for calendar view
        if (!Schema::hasTable('availability_calendar')) {
            Schema::create('availability_calendar', function (Blueprint $table) {
                $table->id();
                $table->enum('service_type', ['dance', 'makeup'])->comment('Type of service');
                $table->unsignedBigInteger('service_id')->comment('ID of the service (dance_service or makeup_service)');
                $table->date('date')->comment('Date for availability');
                $table->integer('available_slots')->comment('Number of available slots for this date');
                $table->timestamps();

                // Composite unique index for service type + service_id + date
                $table->unique(['service_type', 'service_id', 'date'], 'unique_service_availability');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dance_services', 'stock')) {
            Schema::table('dance_services', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }

        Schema::dropIfExists('availability_calendar');
    }
};
