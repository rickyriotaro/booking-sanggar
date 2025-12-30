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
        Schema::table('makeup_services', function (Blueprint $table) {
            // Rename slot column to total_slots
            $table->renameColumn('slot', 'total_slots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('makeup_services', function (Blueprint $table) {
            // Reverse rename
            $table->renameColumn('total_slots', 'slot');
        });
    }
};
