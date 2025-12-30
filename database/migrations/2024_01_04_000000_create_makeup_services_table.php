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
        Schema::create('makeup_services', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->enum('category', ['SD', 'SMP', 'SMA', 'Wisuda', 'Acara Umum']);
            $table->decimal('price', 10, 2);
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
        Schema::dropIfExists('makeup_services');
    }
};
