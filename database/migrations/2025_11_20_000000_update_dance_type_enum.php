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
        // Update dance_type enum to include more dance styles
        Schema::table('dance_services', function (Blueprint $table) {
            // Drop old enum and create new one with more options
            // Note: We need to modify the column directly using raw SQL for MySQL enum
            $table->dropColumn('dance_type');
        });

        Schema::table('dance_services', function (Blueprint $table) {
            $table->enum('dance_type', [
                'Tradisional',
                'Modern',
                'Kontemporer',
                'Kreasi Baru',
                'Zapin',
                'Joget',
                'Tari Melayu',
                'Tari Jawa',
                'Tari Bali',
                'Tari Minangkabau',
                'Tari Sunda',
                'Tari Sulawesi',
                'Tari Dayak',
                'Tari Irian',
                'Hip Hop',
                'Jazz',
                'Ballet',
                'Contemporary Dance',
                'Belly Dance',
                'Lainnya'
            ])->after('package_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dance_services', function (Blueprint $table) {
            $table->dropColumn('dance_type');
        });

        Schema::table('dance_services', function (Blueprint $table) {
            $table->enum('dance_type', ['Tradisional', 'Modern', 'Kontemporer', 'Kreasi Baru'])
                ->after('package_name');
        });
    }
};
