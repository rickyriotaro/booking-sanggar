<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\Order;
use App\Models\StockLog;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AutoReturnDeliveries extends Command
{
    protected $signature = 'orders:auto-return';

    protected $description = 'Auto-mark orders as returned when end_date passed, recalculate snapshots';

    public function handle()
    {
        $this->info('Checking for overdue deliveries...');

        // Find orders where ALL items have passed their item_end_date
        // Support both per-item dates (Phase 4) and order-level dates (legacy)
        $allOrders = Order::where('status', 'paid')
            ->where('return_status', 'belum')
            ->with('orderDetails')
            ->get();

        $overdueOrders = $allOrders->filter(function($order) {
            // Check if ALL items in this order have passed their end date/time
            foreach ($order->orderDetails as $detail) {
                // For jasa (tari/rias) with service_duration, calculate actual return datetime
                $returnDateTime = null;
                
                if (in_array($detail->service_type, ['tari', 'rias']) && $detail->service_duration) {
                    // Jasa dengan durasi: gunakan item_start_date + rental_time + service_duration untuk hitung return datetime
                    if ($detail->rental_time && $detail->item_start_date) {
                        // Parse rental_time (HH:MM)
                        $rentalTime = \Carbon\Carbon::createFromFormat('H:i', $detail->rental_time);
                        
                        // Start dengan item_start_date + rental_time
                        $startDateTime = \Carbon\Carbon::parse($detail->item_start_date)
                            ->setHour($rentalTime->hour)
                            ->setMinute($rentalTime->minute)
                            ->setSecond(0);
                        
                        // Add service_duration (minutes) to get return datetime
                        // This properly handles cross-midnight returns
                        $returnDateTime = $startDateTime->copy()->addMinutes((int)$detail->service_duration);
                    }
                } else if ($detail->return_time && $detail->item_start_date) {
                    // Untuk costume atau jasa dengan return_time: gunakan item_end_date + return_time
                    // (return_time adalah waktu kapan item harus dikembalikan di item_end_date)
                    $endDateStr = $detail->item_end_date ?? $order->end_date;
                    $returnDateTime = \Carbon\Carbon::parse("{$endDateStr} {$detail->return_time}");
                }
                
                // Jika tidak ada return_time, gunakan item_end_date dengan end of day
                if (!$returnDateTime) {
                    $itemEndDate = $detail->item_end_date ?? $order->end_date;
                    $returnDateTime = \Carbon\Carbon::parse($itemEndDate)->endOfDay();
                }
                
                // Check apakah return time sudah PASSED (belum overdue = return time >= now)
                if ($returnDateTime > now()) {
                    $this->line("⏳ Order {$order->order_code}: Item {$detail->service_type}#{$detail->detail_id} still pending (deadline: {$returnDateTime->format('Y-m-d H:i')})");
                    return false;
                }
            }
            // All items have passed their end date/time
            return true;
        });

        if ($overdueOrders->isEmpty()) {
            $this->info('No overdue orders found.');
            return 0;
        }

        $count = 0;
        DB::beginTransaction();

        try {
            foreach ($overdueOrders as $order) {
                // Log per-item dates for debugging
                $itemsInfo = $order->orderDetails->map(function($detail) {
                    $itemEndDate = $detail->item_end_date ?? 'N/A';
                    return "{$detail->service_type}#{$detail->detail_id} (deadline: {$itemEndDate})";
                })->implode(', ');
                
                $this->line("✅ Auto-returning order {$order->order_code}: {$itemsInfo}");

                // Update return status to sudah (returned)
                $order->update(['return_status' => 'sudah']);

                // Recalculate snapshots to reflect available stock
                // When return_status changes to 'sudah', snapshot.recalculate() will:
                // - Exclude this order from stok_from_orders count
                // - Update sisa_stok_tersedia to reflect available stock (back to admin_stock)
                foreach ($order->orderDetails as $detail) {
                    // BARU: Also update per-item return status
                    if (Schema::hasColumn('order_details', 'item_return_status')) {
                        $detail->update([
                            'item_return_status' => 'sudah',
                            'item_return_date' => now()->format('Y-m-d')
                        ]);
                    }

                    $snapshot = StockSnapshot::where('service_type', $detail->service_type)
                        ->where('service_id', $detail->detail_id)
                        ->first();
                    
                    if ($snapshot) {
                        $snapshot->recalculate();
                        $this->line("  📊 Recalculated {$detail->service_type} #{$detail->detail_id}");
                    }
                }

                Log::info("✅ Auto-returned order {$order->order_code} with items: {$itemsInfo}");
                $count++;
            }

            DB::commit();
            $this->info("✅ Auto-returned {$count} order(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error("Auto-return failed: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return 1;
        }

        return 0;
    }
}
