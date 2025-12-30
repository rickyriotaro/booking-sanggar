<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     * 
     * Tambahkan kolom untuk menyimpan status dan tanggal pengembalian per-item:
     * - item_return_date: Tanggal sebenarnya item dikembalikan (nullable)
     * - item_return_status: Status pengembalian item (belum|sudah|terlambat)
     * 
     * Alasan: Dengan per-item dates, kita perlu track return status per-item juga.
     * Contoh: Item A deadline Nov 27 (sudah dikembalikan Nov 27),
     *         Item B deadline Nov 30 (belum dikembalikan sampai Nov 31)
     * Ini memudahkan:
     * 1. Auto-return sesuai per-item deadline (tidak perlu nunggu item lain)
     * 2. Calculate late fees per-item
     * 3. User bisa lihat status return masing-masing item
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->date('item_return_date')->nullable()->after('item_end_date')->comment('Tanggal sebenarnya item dikembalikan');
            $table->enum('item_return_status', ['belum', 'sudah', 'terlambat'])->default('belum')->after('item_return_date')->comment('Status pengembalian per-item: belum|sudah|terlambat');
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['item_return_date', 'item_return_status']);
        });
    }
};
