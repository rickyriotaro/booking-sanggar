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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('image_name')->nullable()->after('message');
            $table->string('image_path')->nullable()->after('image_name');
            $table->bigInteger('image_size')->nullable()->after('image_path');
            
            // Index untuk query cepat
            $table->index('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['image_path']);
            $table->dropColumn(['image_name', 'image_path', 'image_size']);
        });
    }
};
