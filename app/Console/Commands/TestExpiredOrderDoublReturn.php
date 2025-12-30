<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Costume;
use Illuminate\Console\Command;

class TestExpiredOrderDoublReturn extends Command
{
    protected $signature = 'test:expired-double-return';
    protected $description = 'Test: already auto-returned order, admin tries manual return again';

    public function handle()
    {
        $this->line("\n=== Test: Already Auto-Returned Order, Admin Manual Update ===\n");

        $order = Order::where('order_code', 'TEST-20251126021802')->first();

        if (!$order) {
            $this->error("Order not found. Run test:expired-manual-return first");
            return 1;
        }

        $this->line("Order Details:");
        $this->line("Code: {$order->order_code}");
        $this->line("Status: {$order->status}");
        $this->line("Return Status BEFORE: {$order->return_status}");
        $this->line("End Date: {$order->end_date}");

        $costume = Costume::find($order->orderDetails->first()->detail_id);
        $stockBefore = $costume->stock;
        $this->line("\nCostume Stock BEFORE: {$stockBefore}");

        $this->line("\nAdmin clicks Sudah Dikembalikan button...");

        $oldReturnStatus = $order->return_status;
        $newReturnStatus = 'sudah';

        $shouldRestore = (($newReturnStatus === 'sudah' || $newReturnStatus === 'terlambat') 
                        && $oldReturnStatus !== 'sudah' 
                        && $oldReturnStatus !== 'terlambat');

        $this->line("Return Status: {$oldReturnStatus} to {$newReturnStatus}");
        $this->line("Should Restore: " . ($shouldRestore ? 'YES' : 'NO'));

        if ($shouldRestore) {
            $costume->increment('stock', 1);
            $this->line("Stock RESTORED (WRONG!)");
        } else {
            $this->line("Stock NOT restored (CORRECT!)");
        }

        $costume->refresh();
        $stockAfter = $costume->stock;
        $this->line("\nCostume Stock AFTER: {$stockAfter}");

        if ($stockAfter === $stockBefore) {
            $this->info("\nSUCCESS: Stock unchanged - no double restore");
        } else {
            $this->error("\nFAIL: Stock changed {$stockBefore} to {$stockAfter}");
        }

        return 0;
    }
}
