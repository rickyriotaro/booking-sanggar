<?php

namespace App\Console\Commands;

use App\Models\MakeupService;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class TestMakeupUpdate extends Command
{
    protected $signature = 'test:makeup-update';
    protected $description = 'Test makeup service total_slots update triggers observer';

    public function handle()
    {
        $this->info("\n=== Testing Makeup Service Update ===\n");

        $makeup = MakeupService::first();
        if (!$makeup) {
            $this->error("No makeup service found");
            return;
        }

        $this->info("Makeup: " . $makeup->package_name);
        $this->info("Current total_slots: " . $makeup->total_slots);

        // Get snapshot before update
        $snapshot = StockSnapshot::where('service_type', 'rias')
            ->where('service_id', $makeup->id)
            ->first();

        if ($snapshot) {
            $this->info("Before update - Snapshot stok_by_admin: " . $snapshot->stok_by_admin);
        }

        // Update total_slots
        $newSlots = 77;
        $this->info("\nUpdating total_slots to " . $newSlots . "...");
        $makeup->update(['total_slots' => $newSlots]);

        // Check after update
        $makeup->refresh();
        $this->info("After update - Makeup total_slots: " . $makeup->total_slots);

        $snapshot->refresh();
        $this->info("After update - Snapshot stok_by_admin: " . $snapshot->stok_by_admin);
        $this->info("After update - Snapshot sisa_stok_tersedia: " . $snapshot->sisa_stok_tersedia);

        if ($snapshot->stok_by_admin === $newSlots) {
            $this->info("\nSUCCESS - Observer triggered and updated snapshot\n");
        } else {
            $this->error("\nFAILED - Snapshot not updated\n");
        }
    }
}
