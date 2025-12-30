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
        Schema::table('transactions', function (Blueprint $table) {
            // Add fields untuk custom payment UI
            $table->string('payment_channel', 100)->nullable()->after('payment_method'); // va, transfer_bank, e_wallet, credit_card
            $table->string('bank_code', 50)->nullable()->after('payment_channel'); // bca, bni, permata, etc
            $table->string('account_name', 255)->nullable()->after('bank_code'); // nama pemilik rekening
            $table->string('instruction_text', 255)->nullable()->after('account_name'); // instruksi pembayaran
            $table->json('payment_details')->nullable()->after('instruction_text'); // store additional payment info as JSON
            $table->datetime('expires_at')->nullable()->after('paid_at'); // expiry time untuk payment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_channel',
                'bank_code',
                'account_name',
                'instruction_text',
                'payment_details',
                'expires_at',
            ]);
        });
    }
};
