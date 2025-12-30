<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPendingOrders extends Command
{
    protected $signature = 'check:pending-orders';
    protected $description = 'Check all pending orders and their payment expiry status';

    public function handle()
    {
        $this->line("\n=== Pending Orders Status ===\n");

        $orders = Order::where('status', 'pending')
            ->with(['transaction', 'orderDetails'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info("No pending orders");
            return 0;
        }

        foreach ($orders as $order) {
            $this->line("Order: {$order->order_code}");
            $this->line("Status: {$order->status}");
            $this->line("Created: {$order->created_at->format('Y-m-d H:i:s')}");

            if ($order->transaction && $order->transaction->expires_at) {
                $expiresAt = Carbon::parse($order->transaction->expires_at);
                $minsAgo = $expiresAt->diffInMinutes(now());
                $isExpired = now()->isAfter($expiresAt);

                $this->line("Expires At: {$expiresAt->format('Y-m-d H:i:s')}");
                $this->line("Status: " . ($isExpired ? "✓ EXPIRED ({$minsAgo} mins ago)" : "⏳ ACTIVE (in {$minsAgo} mins)"));
            } else {
                $this->line("Expires At: No expiry set");
            }

            foreach ($order->orderDetails as $detail) {
                $serviceName = $detail->service ? ($detail->service->package_name ?? $detail->service->name) : "Unknown";
                $this->line("  - {$detail->service_type}: {$serviceName} (qty: {$detail->quantity})");
            }

            $this->line("");
        }

        return 0;
    }
}
