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
        // Modify sender_type enum to include 'system'
        // MySQL approach for modifying enum
        DB::statement("ALTER TABLE chat_messages MODIFY COLUMN sender_type ENUM('user', 'ai', 'admin', 'system') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original (remove 'system')
        DB::statement("ALTER TABLE chat_messages MODIFY COLUMN sender_type ENUM('user', 'ai', 'admin') NOT NULL");
    }
};
