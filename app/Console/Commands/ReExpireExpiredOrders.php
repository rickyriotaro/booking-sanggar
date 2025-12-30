<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReExpireExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:re-expire {--force : Force re-expire all expired orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-process expired orders to properly set return_status and recalculate snapshots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Finding all expired orders...');

        // Find all orders with status 'expired' but return_status != 'terlambat'
        $expiredOrders = Order::where('status', 'expired')
            ->where(function ($query) {
                $query->where('return_status', '!=', 'terlambat')
                      ->orWhereNull('return_status');
            })
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('✅ No expired orders need re-processing.');
            return 0;
        }

        $this->info("Found {$expiredOrders->count()} expired orders to process.");
        
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to re-process these orders?')) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $count = 0;
        DB::beginTransaction();

        try {
            foreach ($expiredOrders as $order) {
                // Update return_status to terlambat (expired, cannot return)
                $order->update(['return_status' => 'terlambat']);

                // Recalculate snapshots to restore stock/slots availability
                foreach ($order->orderDetails as $detail) {
                    $snapshot = StockSnapshot::where('service_type', $detail->service_type)
                        ->where('service_id', $detail->detail_id)
                        ->first();
                    
                    if ($snapshot) {
                        $snapshot->recalculate();
                        $this->line("  {$detail->service_type} #{$detail->detail_id} snapshot recalculated");
                    }
                }

                Log::info("Re-processed expired order {$order->order_code} - set return_status to terlambat");
                $this->line("✓ Order #{$order->id} ({$order->order_code}) re-processed");
                $count++;
            }

            DB::commit();
            $this->info("✅ Successfully re-processed {$count} expired order(s) and recalculated snapshots.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error re-processing orders: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
