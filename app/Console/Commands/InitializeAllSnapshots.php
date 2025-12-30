<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\DanceService;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeAllSnapshots extends Command
{
    protected $signature = 'snapshot:init-all';
    protected $description = 'Initialize stock snapshots for all services from scratch';

    public function handle()
    {
        $this->info('Initializing all stock snapshots...\n');

        try {
            // Delete all existing snapshots
            $deleted = StockSnapshot::count();
            StockSnapshot::truncate();
            $this->line("Cleared {$deleted} existing snapshots");

            $count = 0;

            // Create snapshots for Costumes
            $costumes = Costume::all();
            $this->line("\nCreating snapshots for " . $costumes->count() . " costumes...");
            foreach ($costumes as $costume) {
                $costumeName = $costume->name ?? "Costume #{$costume->id}";
                $snapshot = StockSnapshot::create([
                    'service_type' => 'kostum',
                    'service_id' => $costume->id,
                    'service_name' => $costumeName,
                    'stok_by_admin' => $costume->stock,
                    'stok_from_orders' => 0,
                    'sisa_stok_tersedia' => $costume->stock,
                    'admin_history' => json_encode([])
                ]);
                $snapshot->recalculate();
                $this->line("  ✓ {$costumeName}");
                $count++;
            }

            // Create snapshots for Makeup Services
            $makeups = MakeupService::all();
            $this->line("\nCreating snapshots for " . $makeups->count() . " makeup services...");
            foreach ($makeups as $makeup) {
                $makeupName = $makeup->name ?? "Makeup #{$makeup->id}";
                $snapshot = StockSnapshot::create([
                    'service_type' => 'rias',
                    'service_id' => $makeup->id,
                    'service_name' => $makeupName,
                    'stok_by_admin' => $makeup->total_slots,
                    'stok_from_orders' => 0,
                    'sisa_stok_tersedia' => $makeup->total_slots,
                    'admin_history' => json_encode([])
                ]);
                $snapshot->recalculate();
                $this->line("  ✓ {$makeupName}");
                $count++;
            }

            // Create snapshots for Dance Services
            $dances = DanceService::all();
            $this->line("\nCreating snapshots for " . $dances->count() . " dance services...");
            foreach ($dances as $dance) {
                $danceName = $dance->package_name ?? "Dance #{$dance->id}";
                $snapshot = StockSnapshot::create([
                    'service_type' => 'tari',
                    'service_id' => $dance->id,
                    'service_name' => $danceName,
                    'stok_by_admin' => $dance->stock,
                    'stok_from_orders' => 0,
                    'sisa_stok_tersedia' => $dance->stock,
                    'admin_history' => json_encode([])
                ]);
                $snapshot->recalculate();
                $this->line("  ✓ {$danceName}");
                $count++;
            }

            $this->info("\n✅ Successfully created and recalculated {$count} snapshots!");

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
