<?php

namespace App\Console\Commands;

use App\Models\DanceService;
use App\Models\Order;
use Illuminate\Console\Command;

class CheckDanceServiceAvailability extends Command
{
    protected $signature = 'check:dance-availability';
    protected $description = 'Check dance service availability status';

    public function handle()
    {
        $danceServices = DanceService::all();

        foreach ($danceServices as $service) {
            $this->line("\n=== {$service->package_name} (ID: {$service->id}) ===");
            $this->line("Is Available (flag): " . ($service->is_available ? '✓ YES' : '✗ NO'));
            $this->line("Stock: {$service->stock}");
            $this->line("Availability Status: " . $service->getAvailabilityStatus());

            // Check active orders
            $activeOrders = Order::whereHas('orderDetails', function ($q) use ($service) {
                $q->where('service_type', 'tari')
                    ->where('detail_id', $service->id);
            })
                ->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready'])
                ->where(function ($q) {
                    $q->where('return_status', '!=', 'sudah')
                        ->where('return_status', '!=', 'terlambat')
                        ->orWhereNull('return_status');
                })
                ->get();

            $this->line("Active Orders (pending return): " . count($activeOrders));
            foreach ($activeOrders as $order) {
                $detail = $order->orderDetails()
                    ->where('service_type', 'tari')
                    ->where('detail_id', $service->id)
                    ->first();
                $this->line("  - Order #{$order->order_code}: {$order->start_date} to {$order->end_date}, Qty: {$detail->quantity}, Return Status: {$order->return_status}");
            }

            // Check completed orders
            $completedOrders = Order::whereHas('orderDetails', function ($q) use ($service) {
                $q->where('service_type', 'tari')
                    ->where('detail_id', $service->id);
            })
                ->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready'])
                ->whereIn('return_status', ['sudah', 'terlambat'])
                ->get();

            $this->line("Completed Orders (returned): " . count($completedOrders));
            foreach ($completedOrders as $order) {
                $detail = $order->orderDetails()
                    ->where('service_type', 'tari')
                    ->where('detail_id', $service->id)
                    ->first();
                $this->line("  - Order #{$order->order_code}: {$order->start_date} to {$order->end_date}, Qty: {$detail->quantity}, Return Status: {$order->return_status}");
            }

            // Test getAvailableSlots for today
            $today = now()->format('Y-m-d');
            $availableSlots = $service->getAvailableSlots($today, $today);
            $this->line("Available slots for {$today}: {$availableSlots}");
        }
    }
}
