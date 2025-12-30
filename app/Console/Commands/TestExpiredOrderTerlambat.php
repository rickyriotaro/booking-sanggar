<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Costume;
use Illuminate\Console\Command;

class TestExpiredOrderTerlambat extends Command
{
    protected $signature = 'test:expired-terlambat';
    protected $description = 'Test: already auto-returned, admin updates to terlambat';

    public function handle()
    {
        $this->line("\n=== Test: Already Auto-Returned Order, Admin Changes to Terlambat ===\n");

        $order = Order::where('order_code', 'TEST-20251126021802')->first();

        if (!$order) {
            $this->error("Order not found");
            return 1;
        }

        $this->line("Order: {$order->order_code}");
        $this->line("Return Status BEFORE: {$order->return_status}");

        $costume = Costume::find($order->orderDetails->first()->detail_id);
        $stockBefore = $costume->stock;
        $this->line("Stock BEFORE: {$stockBefore}");

        $this->line("\nAdmin changes to Terlambat (return date 28 Nov)...");

        $oldReturnStatus = $order->return_status;
        $newReturnStatus = 'terlambat';

        $check1 = ($newReturnStatus === 'sudah' || $newReturnStatus === 'terlambat');
        $check2 = ($oldReturnStatus !== 'sudah');
        $check3 = ($oldReturnStatus !== 'terlambat');
        $shouldRestore = ($check1 && $check2 && $check3);

        $this->line("Change: {$oldReturnStatus} to {$newReturnStatus}");
        $this->line("Should Restore: " . ($shouldRestore ? 'YES' : 'NO'));

        if ($shouldRestore) {
            $costume->increment('stock', 1);
        }

        $costume->refresh();
        $stockAfter = $costume->stock;

        if ($stockAfter === $stockBefore) {
            $this->info("\nCORRECT: Stock unchanged (no double restore) - stayed at {$stockBefore}");
        } else {
            $this->error("\nFAIL: Stock changed {$stockBefore} to {$stockAfter}");
        }

        return 0;
    }
}
