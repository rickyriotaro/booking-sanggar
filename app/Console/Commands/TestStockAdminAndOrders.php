<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockSnapshot;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Test untuk verify:
 * 1. Admin edit stok → stok_by_admin berubah, sisa_stok_tersedia recalculate
 * 2. User buat order → stok_by_admin TIDAK boleh berubah, hanya snapshot yang update
 * 3. Order end date lewat → sisa_stok_setelah_booking = stok_by_admin
 */
class TestStockAdminAndOrders extends Command
{
    protected $signature = 'test:stock-admin-orders';
    protected $description = 'Test stock_by_admin immutability and order impact on snapshot';

    public function handle()
    {
        $this->info("\n=== TEST: ADMIN STOCK vs ORDER IMPACT ===\n");

        // Setup
        $costume = Costume::firstOrCreate(
            ['costume_name' => 'Test Baju Admin'],
            ['rental_price' => 50000, 'stock' => 10, 'description' => 'Test']
        );

        $user = User::firstOrCreate(
            ['email' => 'testorder@test.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'phone' => '08123456789']
        );

        // Init snapshot
        $snapshot = StockSnapshot::firstOrCreate(
            ['service_type' => 'kostum', 'service_id' => $costume->id],
            [
                'service_name' => $costume->costume_name,
                'stok_by_admin' => 10,
                'stok_from_orders' => 0,
                'sisa_stok_tersedia' => 10,
                'sisa_stok_setelah_booking' => 10,
                'admin_history' => json_encode([]),
            ]
        );

        $this->info("Costume: {$costume->costume_name} (ID {$costume->id})");
        $this->info("Initial state: costume.stock = {$costume->stock}, snapshot.stok_by_admin = {$snapshot->stok_by_admin}\n");

        // TEST 1: Admin edit stok dari 10 ke 8
        $this->info("--- TEST 1: Admin edit stok 10 → 8 ---");
        $costume->update(['stock' => 8]);
        $snapshot->refresh();

        $this->info("After admin edit:");
        $this->info("  costume.stock: {$costume->stock}");
        $this->info("  snapshot.stok_by_admin: {$snapshot->stok_by_admin}");
        $this->info("  snapshot.sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia}");

        if ($costume->stock == 8 && $snapshot->stok_by_admin == 8 && $snapshot->sisa_stok_tersedia == 8) {
            $this->info("✓ Test 1 PASSED: Admin edit works correctly\n");
        } else {
            $this->error("✗ Test 1 FAILED\n");
            return 1;
        }

        // TEST 2: User create order qty 2
        $this->info("--- TEST 2: User order qty 2 untuk hari ini ---");
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => 'ORD-TEST-' . uniqid(),
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'pending',
            'return_status' => 'belum',
            'total_price' => 100000,
            'total_amount' => 100000,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'service_type' => 'kostum',
            'detail_id' => $costume->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        // Manually trigger snapshot recalculation (normally done by service)
        $snapshot->recalculate();

        $this->info("After order created:");
        $this->info("  costume.stock: {$costume->stock} (should still be 8 - NOT changed!)");
        $this->info("  snapshot.stok_by_admin: {$snapshot->stok_by_admin} (should be 8 - NOT changed!)");
        $this->info("  snapshot.stok_from_orders: {$snapshot->stok_from_orders} (should be 2)");
        $this->info("  snapshot.sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia} (should be 6 = 8-2)");

        if ($costume->stock == 8 && $snapshot->stok_by_admin == 8 && $snapshot->stok_from_orders == 2 && $snapshot->sisa_stok_tersedia == 6) {
            $this->info("✓ Test 2 PASSED: costume.stock immutable, only snapshot updates\n");
        } else {
            $this->error("✗ Test 2 FAILED");
            $this->error("  Expected: costume.stock=8, stok_by_admin=8, stok_from_orders=2, sisa_stok_tersedia=6");
            $this->error("  Got: costume.stock={$costume->stock}, stok_by_admin={$snapshot->stok_by_admin}, stok_from_orders={$snapshot->stok_from_orders}, sisa_stok_tersedia={$snapshot->sisa_stok_tersedia}\n");
            return 1;
        }

        // TEST 3: Admin edit lagi stok 8 → 12
        $this->info("--- TEST 3: Admin edit stok 8 → 12 (saat ada order) ---");
        $costume->update(['stock' => 12]);
        $snapshot->refresh();

        $this->info("After second admin edit:");
        $this->info("  costume.stock: {$costume->stock}");
        $this->info("  snapshot.stok_by_admin: {$snapshot->stok_by_admin}");
        $this->info("  snapshot.stok_from_orders: {$snapshot->stok_from_orders} (should still be 2)");
        $this->info("  snapshot.sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia} (should be 10 = 12-2)");

        if ($costume->stock == 12 && $snapshot->stok_by_admin == 12 && $snapshot->stok_from_orders == 2 && $snapshot->sisa_stok_tersedia == 10) {
            $this->info("✓ Test 3 PASSED: Admin can increase stok independently\n");
        } else {
            $this->error("✗ Test 3 FAILED\n");
            return 1;
        }

        // TEST 4: Check sisa_stok_setelah_booking
        $this->info("--- TEST 4: sisa_stok_setelah_booking (after end_date) ---");
        $this->info("  sisa_stok_setelah_booking: {$snapshot->sisa_stok_setelah_booking} (should be 12 = stok_by_admin)");

        if ($snapshot->sisa_stok_setelah_booking == 12) {
            $this->info("✓ Test 4 PASSED: sisa_stok_setelah_booking = stok_by_admin\n");
        } else {
            $this->error("✗ Test 4 FAILED\n");
            return 1;
        }

        $this->info("<fg=green>✓ ALL TESTS PASSED - Stock behavior correct!</>\n");
        return 0;
    }
}
