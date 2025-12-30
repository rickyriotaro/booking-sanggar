<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

/**
 * Test admin auto-save snapshot functionality
 * 
 * Scenario:
 * - Admin edit costume stock dari 10 menjadi 8
 * - System harus auto-save ke snapshot
 * - admin_history harus terupdate
 * - sisa_stok_tersedia harus recalculate
 */
class TestAdminAutoSave extends Command
{
    protected $signature = 'test:admin-auto-save';
    protected $description = 'Test admin auto-save stock to snapshot via Observer';

    public function handle()
    {
        $this->info("\n=== TESTING ADMIN AUTO-SAVE FUNCTIONALITY ===\n");

        // Get or create test costume
        $costume = Costume::firstOrCreate(
            ['costume_name' => 'Test Baju Merah'],
            [
                'rental_price' => 50000,
                'stock' => 10,
                'description' => 'Test costume',
            ]
        );

        $this->info("Costume: {$costume->costume_name} (ID {$costume->id})");
        $this->info("Current stock: {$costume->stock}");

        // Initialize snapshot jika belum ada
        $snapshot = StockSnapshot::firstOrCreate(
            ['service_type' => 'kostum', 'service_id' => $costume->id],
            [
                'service_name' => $costume->costume_name,
                'stok_by_admin' => $costume->stock,
                'stok_from_orders' => 0,
                'sisa_stok_tersedia' => $costume->stock,
                'admin_history' => json_encode([]),
            ]
        );

        $this->info("\n--- BEFORE EDIT ---");
        $this->info("stok_by_admin: {$snapshot->stok_by_admin}");
        $this->info("stok_from_orders: {$snapshot->stok_from_orders}");
        $this->info("sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia}");
        $history = $snapshot->admin_history;
        if (is_string($history)) {
            $history = json_decode($history, true) ?? [];
        }
        $this->info("admin_history entries: " . count($history));

        // ✓ SIMULATE: Admin edit costume stock dari 10 ke 8
        $this->info("\n--- ADMIN EDITS COSTUME STOCK: 10 → 8 ---");
        $costume->update(['stock' => 8]);
        $this->info("✓ Costume stock updated to 8");

        // Refresh snapshot dari database
        $snapshot->refresh();

        $this->info("\n--- AFTER OBSERVER TRIGGERED ---");
        $this->info("stok_by_admin: {$snapshot->stok_by_admin} (harus 8)");
        $this->info("stok_from_orders: {$snapshot->stok_from_orders}");
        $this->info("sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia} (harus 8)");
        $history = $snapshot->admin_history;
        if (is_string($history)) {
            $history = json_decode($history, true) ?? [];
        }
        $this->info("admin_history entries: " . count($history));

        // Verify results
        $this->info("\n--- VERIFICATION ---");
        $passed = true;

        if ($snapshot->stok_by_admin != 8) {
            $this->error("❌ stok_by_admin should be 8, got {$snapshot->stok_by_admin}");
            $passed = false;
        } else {
            $this->info("✓ stok_by_admin = 8 ✓");
        }

        if ($snapshot->sisa_stok_tersedia != 8) {
            $this->error("❌ sisa_stok_tersedia should be 8, got {$snapshot->sisa_stok_tersedia}");
            $passed = false;
        } else {
            $this->info("✓ sisa_stok_tersedia = 8 ✓");
        }

        if (empty($snapshot->admin_history)) {
            $this->error("❌ admin_history should have entries");
            $passed = false;
        } else {
            $h = $snapshot->admin_history;
            if (is_string($h)) {
                $h = json_decode($h, true) ?? [];
            }
            $this->info("✓ admin_history has " . count($h) . " entries ✓");
            if (!empty($h)) {
                $lastEntry = end($h);
                $this->info("  Last entry: qty={$lastEntry['qty']}, reason={$lastEntry['reason']}");
            }
        }

        // Test edit lagi: 8 → 15
        $this->info("\n--- SECOND EDIT: 8 → 15 ---");
        $costume->update(['stock' => 15]);
        $snapshot->refresh();

        $this->info("stok_by_admin: {$snapshot->stok_by_admin} (harus 15)");
        $this->info("sisa_stok_tersedia: {$snapshot->sisa_stok_tersedia} (harus 15)");
        $h = $snapshot->admin_history;
        if (is_string($h)) {
            $h = json_decode($h, true) ?? [];
        }
        $this->info("admin_history entries: " . count($h));

        if ($snapshot->stok_by_admin == 15 && $snapshot->sisa_stok_tersedia == 15) {
            $this->info("✓ Second edit also auto-saved ✓");
        } else {
            $this->error("❌ Second edit failed");
            $passed = false;
        }

        // Final message
        $this->info("\n--- RESULT ---");
        if ($passed) {
            $this->info("<fg=green>✓ ALL TESTS PASSED - Admin auto-save is working!</>\n");
        } else {
            $this->error("<fg=red>✗ Some tests failed</>\n");
        }

        return $passed ? 0 : 1;
    }
}
