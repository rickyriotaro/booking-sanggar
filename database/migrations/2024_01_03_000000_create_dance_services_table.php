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
        Schema::create('dance_services', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->enum('dance_type', ['Tradisional', 'Modern', 'Kontemporer', 'Kreasi Baru']);
            $table->integer('number_of_dancers'); // Wajib ganjil: 3, 5, 7, dst
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(60);
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dance_services');
    }
};
