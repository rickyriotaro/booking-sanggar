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
        Schema::create('makeup_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('makeup_service_id')->constrained()->onDelete('cascade');
            $table->integer('total_slots')->default(1);
            $table->integer('available_slots')->default(1);
            $table->date('slot_date');
            $table->time('slot_time');
            $table->timestamps();
            
            $table->unique(['makeup_service_id', 'slot_date', 'slot_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makeup_slots');
    }
};