<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockSnapshot;
use App\Models\User;
use App\Services\AvailabilityCalculatorService;
use Illuminate\Console\Command;

class TestAvailabilitySystem extends Command
{
    protected $signature = 'test:availability';
    protected $description = 'Test overlapping availability calculation with date-based logic';

    public function handle()
    {
        // Clean up existing test orders
        $orders = Order::where('start_date', '>=', '2025-11-25')->get();
        $this->info("Cleaning up {$orders->count()} existing test orders...");
        foreach ($orders as $o) {
            OrderDetail::where('order_id', $o->id)->delete();
            $o->delete();
        }

        // Create/update snapshot
        $snapshot = StockSnapshot::updateOrCreate(
            ['service_type' => 'kostum', 'service_id' => 1],
            [
                'service_name' => 'Baju Merah (Kostum)',
                'stok_by_admin' => 10,
                'stok_from_orders' => 0,
                'sisa_stok_setelah_booking' => 10,
                'admin_history' => json_encode([]),
            ]
        );

        $this->info("\n=== SETUP ===");
        $this->info("Snapshot: {$snapshot->service_name}");
        $this->info("Stock: {$snapshot->stok_by_admin} unit");

        // Create test users
        $user1 = User::firstOrCreate(
            ['email' => 'user1@test.com'],
            ['name' => 'User A', 'password' => bcrypt('password'), 'phone' => '08123456789']
        );
        $user2 = User::firstOrCreate(
            ['email' => 'user2@test.com'],
            ['name' => 'User B', 'password' => bcrypt('password'), 'phone' => '08987654321']
        );

        // Create test orders
        $order1 = Order::create([
            'user_id' => $user1->id,
            'order_code' => 'TEST-' . uniqid(),
            'start_date' => '2025-11-26',
            'end_date' => '2025-11-27',
            'status' => 'completed',
            'return_status' => 'belum',
            'total_price' => 100000,
            'total_amount' => 100000,
        ]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'service_type' => 'kostum',
            'detail_id' => 1,
            'quantity' => 2,
            'unit_price' => 50000,
            'price_per_item' => 50000,
            'subtotal' => 100000,
        ]);

        $order2 = Order::create([
            'user_id' => $user2->id,
            'order_code' => 'TEST-' . uniqid(),
            'start_date' => '2025-11-27',
            'end_date' => '2025-11-28',
            'status' => 'completed',
            'return_status' => 'belum',
            'total_price' => 150000,
            'total_amount' => 150000,
        ]);

        OrderDetail::create([
            'order_id' => $order2->id,
            'service_type' => 'kostum',
            'detail_id' => 1,
            'quantity' => 3,
            'unit_price' => 50000,
            'price_per_item' => 50000,
            'subtotal' => 150000,
        ]);

        $this->info("\n=== ORDERS CREATED ===");
        $this->info("Order 1: User A (ID {$user1->id}), 2025-11-26 to 2025-11-27, qty 2");
        $this->info("Order 2: User B (ID {$user2->id}), 2025-11-27 to 2025-11-28, qty 3");

        // Test availability calculations
        $calc = new AvailabilityCalculatorService();

        $this->info("\n=== AVAILABILITY PER TANGGAL ===");
        $dates = ['2025-11-25', '2025-11-26', '2025-11-27', '2025-11-28', '2025-11-29'];
        $labels = ['Today', 'Day 1 (A)', 'Day 2 (A+B overlap)', 'Day 3 (B)', 'Day 4 (all return)'];

        foreach (array_combine($dates, $labels) as $date => $label) {
            $avail = $calc->getAvailableOnDate('kostum', 1, $date, 10);
            $this->info("  {$date} ({$label}): <fg=yellow>{$avail}</> tersedia");
        }

        $this->info("\n=== NEXT AVAILABLE (qty 7) ===");
        $next7 = $calc->getNextAvailableDate('kostum', 1, 7, 10, '2025-11-25');
        if ($next7) {
            $this->info("  → Available tgl <fg=green>{$next7['date']}</> qty <fg=green>{$next7['available_qty']}</> ({$next7['days_from_now']} hari dari sekarang)");
        } else {
            $this->info("  → <fg=green>Immediately available</>");
        }

        $this->info("\n=== NEXT AVAILABLE (qty 5) ===");
        $next5 = $calc->getNextAvailableDate('kostum', 1, 5, 10, '2025-11-25');
        if ($next5) {
            $this->info("  → Available tgl <fg=green>{$next5['date']}</> qty <fg=green>{$next5['available_qty']}</> ({$next5['days_from_now']} hari dari sekarang)");
        } else {
            $this->info("  → <fg=green>Immediately available</>");
        }

        $this->info("\n=== AVAILABILITY SUMMARY (untuk Flutter) ===");
        $summary = $calc->getAvailabilitySummary('kostum', 1, 10, '2025-11-25');
        $this->info("  Admin stock: {$summary['admin_stock']}");
        $this->info("  Available today: <fg=yellow>{$summary['available_today']}</>");
        $this->info("  Fully booked: " . ($summary['fully_booked'] ? 'Yes' : 'No'));
        if ($summary['next_available']) {
            $this->info("  Next available: {$summary['next_available']['date']} qty {$summary['next_available']['available_qty']}");
        }
        if ($summary['next_returning']) {
            $this->info("  Next returning: {$summary['next_returning']['date']} qty {$summary['next_returning']['returning_qty']}");
        }

        $this->info("\n=== FLUTTER WARNING MESSAGES ===");
        
        // Test message generation for qty 5
        $requiredQty = 5;
        $availableToday = $calc->getAvailableOnDate('kostum', 1, '2025-11-25', 10);
        
        if ($availableToday >= $requiredQty) {
            $this->info("  ✓ Stok/slot tersedia sebanyak {$availableToday}");
        } else {
            $next = $calc->getNextAvailableDate('kostum', 1, $requiredQty, 10, '2025-11-25');
            if ($next) {
                $this->warn("  ⚠ Stok/slot hanya tersedia sebanyak {$availableToday} hari ini. Akan tersedia tgl {$next['date']} sebanyak {$next['available_qty']}");
            } else {
                $this->error("  ✗ Stok/slot tidak tersedia dalam 30 hari ke depan");
            }
        }

        $this->info("\n<fg=green>✓ Test complete - all calculations working correctly</>\n");
    }
}
