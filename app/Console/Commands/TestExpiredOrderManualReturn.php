<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Costume;
use Illuminate\Console\Command;

class TestExpiredOrderManualReturn extends Command
{
    protected $signature = 'test:expired-manual-return';
    protected $description = 'Test scenario: expired order, manual admin return status update';

    public function handle()
    {
        $this->line("\n=== Test: Expired Order Manual Return ===\n");

        // Scenario 1: Find an order with end_date < today
        $expiredOrder = Order::where('status', 'paid')
            ->where('return_status', 'belum')
            ->whereDate('end_date', '<', now())
            ->first();

        if (!$expiredOrder) {
            // Create one for testing
            $this->line("Creating test order...");
            $costume = Costume::first();
            if (!$costume) {
                $this->error("No costume found");
                return 1;
            }

            // Create order with end_date in past
            $expiredOrder = Order::create([
                'user_id' => 1,
                'order_code' => 'TEST-' . now()->format('YmdHis'),
                'start_date' => now()->subDays(5),
                'end_date' => now()->subDays(2), // 2 days ago
                'total_price' => 100000,
                'total_amount' => 1,
                'status' => 'paid',
                'return_status' => 'belum'
            ]);

            $expiredOrder->orderDetails()->create([
                'service_type' => 'kostum',
                'detail_id' => $costume->id,
                'quantity' => 1,
                'unit_price' => 100000
            ]);

            $this->line("Created order: {$expiredOrder->order_code}");
        }

        $this->line("\n📋 Order Details:");
        $this->line("Code: {$expiredOrder->order_code}");
        $this->line("Status: {$expiredOrder->status}");
        $this->line("Return Status: {$expiredOrder->return_status}");
        $this->line("End Date: {$expiredOrder->end_date}");
        $this->line("Days Expired: " . now()->diffInDays($expiredOrder->end_date) . " days ago");

        // Get costume before update
        $costume = Costume::find($expiredOrder->orderDetails->first()->detail_id);
        $stockBefore = $costume->stock;
        $this->line("\n📦 Stock Before Update: {$stockBefore}");

        // Simulate admin updating return_status manually
        $this->line("\n⏳ Simulating admin update return_status to 'sudah'...");
        $oldReturnStatus = $expiredOrder->return_status;
        $expiredOrder->update(['return_status' => 'sudah']);

        // Check if stock would be restored
        $shouldRestore = ($oldReturnStatus !== 'sudah' && $oldReturnStatus !== 'terlambat');
        $this->line("Old Return Status: {$oldReturnStatus}");
        $this->line("Should Restore Stock: " . ($shouldRestore ? 'YES' : 'NO'));

        if ($shouldRestore) {
            $costume->increment('stock', 1);
            $this->line("✅ Stock restored!");
        } else {
            $this->line("⛔ Stock NOT restored (already returned via auto-return)");
        }

        // Get costume after update
        $costume->refresh();
        $stockAfter = $costume->stock;
        $this->line("\n📦 Stock After Update: {$stockAfter}");
        $this->line("Stock Change: {$stockBefore} → {$stockAfter}");

        if ($stockAfter === $stockBefore) {
            $this->info("✅ CORRECT: Stock did not change (no double restore)");
        } else {
            $this->warn("⚠️ Stock changed: {$stockBefore} → {$stockAfter}");
        }

        return 0;
    }
}
