<?php

namespace App\Console\Commands;

use App\Models\DanceService;
use App\Models\Order;
use Illuminate\Console\Command;

class TestDanceAvailability extends Command
{
    protected $signature = 'test:dance-availability';
    protected $description = 'Test dance service availability with paid orders';

    public function handle()
    {
        $this->info("\n=== Testing Dance Service Availability ===\n");

        $dance = DanceService::first();
        if (!$dance) {
            $this->error("No dance service found");
            return;
        }

        $this->line("Dance Service: " . $dance->package_name);
        $this->line("is_available flag: " . ($dance->is_available ? 'Yes' : 'No'));

        // Check active orders
        $pendingOrders = Order::whereHas('orderDetails', function ($q) {
            $q->where('service_type', 'tari')->where('detail_id', 1);
        })
            ->whereIn('status', ['pending', 'paid'])
            ->count();

        $this->line("Active orders (pending + paid): " . $pendingOrders);

        // Test availability
        $isAvailable = $dance->isAvailable();
        $this->line("isAvailable() result: " . ($isAvailable ? 'AVAILABLE' : 'NOT AVAILABLE'));

        if ($pendingOrders > 0 && $isAvailable) {
            $this->error("\nERROR: Service should NOT be available when there are active orders!");
        } elseif ($pendingOrders === 0 && !$isAvailable) {
            $this->error("\nERROR: Service should be available when there are no active orders!");
        } else {
            $this->info("\nSUCCESS: Availability status is correct!");
        }

        $this->info();
    }
}
