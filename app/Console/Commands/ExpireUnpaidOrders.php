<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unpaid orders where transaction has expired (expires_at < now), auto-restore snapshots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for expired unpaid orders...');

        // Find orders with status 'pending' yang transaksinya sudah expired
        $expiredOrders = Order::where('status', 'pending')
            ->whereHas('transaction', function ($query) {
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<', now());
            })
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('✅ No expired orders found.');
            return 0;
        }

        $count = 0;
        DB::beginTransaction();

        try {
            foreach ($expiredOrders as $order) {
                // Update order status to expired
                $order->update([
                    'status' => 'expired',
                    'return_status' => 'gagal'  // Mark as payment failed (payment expired)
                ]);

                // Update transaction status
                $order->transaction()->update(['pg_status' => 'expired']);

                // Recalculate snapshots to restore stock availability
                // When return_status changes to 'terlambat', snapshot.recalculate() will:
                // - Exclude this order from stok_from_orders count (filtered by return_status != 'terlambat')
                // - Update sisa_stok_tersedia to reflect available stock (restored)
                foreach ($order->orderDetails as $detail) {
                    $snapshot = StockSnapshot::where('service_type', $detail->service_type)
                        ->where('service_id', $detail->detail_id)
                        ->first();
                    
                    if ($snapshot) {
                        $snapshot->recalculate();
                        $this->line("  {$detail->service_type} #{$detail->detail_id} snapshot recalculated");
                    }
                }

                Log::info("Auto-expired unpaid order {$order->order_code} and recalculated snapshots");
                $this->line("⏰ Expired: Order #{$order->id} ({$order->order_code})");
                $count++;
            }

            DB::commit();
            $this->info("✅ Successfully expired {$count} order(s) and recalculated snapshots.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error expiring orders: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
