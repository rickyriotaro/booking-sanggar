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
        // Drop old table
        Schema::dropIfExists('stock_snapshots');
        
        // Create new table with proper structure
        Schema::create('stock_snapshots', function (Blueprint $table) {
            $table->id();
            
            // Service identification
            $table->string('service_type');  // kostum, tari, rias
            $table->unsignedBigInteger('service_id');
            $table->string('service_name');
            
            // ADMIN SET STOCK - Ini yang real dari admin
            // Catat setiap kali admin add/edit
            $table->integer('stok_by_admin');  // Stok asli yang admin set (10 → 12 → 15)
            $table->text('admin_history')->nullable();  // JSON: [{qty: 10, admin_id, date}, {qty: 12, ...}]
            
            // ORDER STOCK - Hasil dari order
            $table->integer('stok_from_orders')->default(0);  // Total qty yang di-order
            $table->dateTime('last_booking_date')->nullable();  // Tgl booking terakhir
            
            // SISA STOK - Hasil perhitungan
            $table->integer('sisa_stok_setelah_booking')->default(0);  // stok_by_admin - stok_from_orders
            
            // Admin tracking
            $table->unsignedBigInteger('last_edited_by_admin')->nullable();  // Siapa yang terakhir edit
            $table->dateTime('last_edited_at')->nullable();  // Kapan terakhir di-edit
            $table->string('edit_reason')->nullable();  // Alasan perubahan
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['service_type', 'service_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_snapshots');
    }
};
