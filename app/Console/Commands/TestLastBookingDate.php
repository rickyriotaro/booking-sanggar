<?php

namespace App\Console\Commands;

use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class TestLastBookingDate extends Command
{
    protected $signature = 'test:last-booking-date';
    protected $description = 'Test last_booking_date calculation';

    public function handle()
    {
        $this->line("\n=== Last Booking Date Test ===\n");

        $snapshots = StockSnapshot::all();

        foreach ($snapshots as $snapshot) {
            $this->line("Service: {$snapshot->service_name} ({$snapshot->service_type})");
            $this->line("Admin Stock: {$snapshot->stok_by_admin}");
            $this->line("Booked Qty: {$snapshot->stok_from_orders}");
            
            if ($snapshot->last_booking_date) {
                $this->line("Last Booking Date: {$snapshot->last_booking_date->format('Y-m-d')}");
            } else {
                $this->line("Last Booking Date: None (no active bookings)");
            }
            
            // Show active orders
            $service = $snapshot->getService();
            if ($service) {
                $activeOrders = $service->orderDetails()
                    ->whereHas('order', function ($q) {
                        $q->where('return_status', '!=', 'sudah')
                          ->where('return_status', '!=', 'terlambat')
                          ->orWhereNull('return_status');
                    })
                    ->with('order')
                    ->get();

                if ($activeOrders->count() > 0) {
                    $this->line("Active Orders:");
                    foreach ($activeOrders as $detail) {
                        $order = $detail->order;
                        $this->line("  - {$order->order_code}: {$order->start_date->format('Y-m-d')} to {$order->end_date->format('Y-m-d')}");
                    }
                } else {
                    $this->line("No active orders");
                }
            }

            $this->line("");
        }

        return 0;
    }
}
