<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CheckOrderStatus extends Command
{
    protected $signature = 'check:order-status {order_code}';
    protected $description = 'Check order status and return_status';

    public function handle()
    {
        $orderCode = $this->argument('order_code');
        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            $this->error("Order {$orderCode} tidak ditemukan");
            return;
        }

        $this->line("\n=== Order {$orderCode} ===");
        $this->line("Status: {$order->status}");
        $this->line("Return Status: {$order->return_status}");
        $this->line("Start Date: {$order->start_date}");
        $this->line("End Date: {$order->end_date}");

        foreach ($order->orderDetails as $detail) {
            $service = $detail->service();
            $this->line("\nService Type: {$detail->service_type}");
            if ($service) {
                $serviceName = $service->package_name ?? $service->name ?? 'N/A';
                $this->line("Service Name: {$serviceName}");
            }
            $this->line("Quantity: {$detail->quantity}");
        }
    }
}
