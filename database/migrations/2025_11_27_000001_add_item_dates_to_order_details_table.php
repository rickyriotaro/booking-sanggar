<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     * 
     * Tambahkan kolom untuk menyimpan tanggal mulai dan berakhir per-item:
     * - item_start_date: Tanggal mulai untuk item tertentu (berbeda per item)
     * - item_end_date: Tanggal berakhir untuk item tertentu (berbeda per item)
     * 
     * Alasan: Setiap item dalam order bisa memiliki tanggal rental yang berbeda-beda.
     * Contoh: Kostum A booking 28-29 Nov, Tari A booking 27 Nov, Rias A booking 30-31 Nov.
     * Ini memudahkan tracking return per-item sesuai tanggal dan jam masing-masing.
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->date('item_start_date')->nullable()->after('return_time')->comment('Tanggal mulai rental untuk item ini');
            $table->date('item_end_date')->nullable()->after('item_start_date')->comment('Tanggal berakhir rental untuk item ini');
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['item_start_date', 'item_end_date']);
        });
    }
};
