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
        Schema::create('stock_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costume_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity_change'); // Negatif (keluar), Positif (masuk)
            $table->date('log_date');
            $table->enum('type', ['out', 'in', 'adjustment'])->default('out');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_log');
    }
};
