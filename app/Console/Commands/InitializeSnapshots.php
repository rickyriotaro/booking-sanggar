<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\DanceService;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class InitializeSnapshots extends Command
{
    protected $signature = 'snapshot:initialize';
    protected $description = 'Initialize stock snapshots for all services without snapshots';

    public function handle()
    {
        $this->info("\n=== Initializing Stock Snapshots ===\n");

        $totalCreated = 0;

        // Initialize Costume Snapshots
        $this->info("Processing Costumes...");
        $costumes = Costume::all();
        $costumeCount = 0;

        foreach ($costumes as $costume) {
            $exists = StockSnapshot::where('service_type', 'kostum')
                ->where('service_id', $costume->id)
                ->exists();

            if (!$exists) {
                try {
                    StockSnapshot::create([
                        'service_type' => 'kostum',
                        'service_id' => $costume->id,
                        'service_name' => $costume->costume_name,
                        'stok_by_admin' => $costume->stock ?? 0,
                        'stok_from_orders' => 0,
                        'sisa_stok_tersedia' => $costume->stock ?? 0,
                        'sisa_stok_setelah_booking' => $costume->stock ?? 0,
                        'admin_history' => json_encode([
                            [
                                'qty' => $costume->stock ?? 0,
                                'admin_id' => null,
                                'reason' => 'System initialization',
                                'date' => now()
                            ]
                        ])
                    ]);

                    $this->line("  OK {$costume->costume_name}");
                    $costumeCount++;
                    $totalCreated++;
                } catch (\Exception $e) {
                    $this->error("  ERROR {$costume->costume_name}: " . $e->getMessage());
                }
            }
        }

        // Initialize Makeup Service Snapshots
        $this->info("\nProcessing Makeup Services...");
        $makeup = MakeupService::all();
        $makeupCount = 0;

        foreach ($makeup as $service) {
            $exists = StockSnapshot::where('service_type', 'rias')
                ->where('service_id', $service->id)
                ->exists();

            if (!$exists) {
                try {
                    StockSnapshot::create([
                        'service_type' => 'rias',
                        'service_id' => $service->id,
                        'service_name' => $service->package_name,
                        'stok_by_admin' => $service->total_slots ?? 0,
                        'stok_from_orders' => 0,
                        'sisa_stok_tersedia' => $service->total_slots ?? 0,
                        'sisa_stok_setelah_booking' => $service->total_slots ?? 0,
                        'admin_history' => json_encode([
                            [
                                'qty' => $service->total_slots ?? 0,
                                'admin_id' => null,
                                'reason' => 'System initialization',
                                'date' => now()
                            ]
                        ])
                    ]);

                    $this->line("  OK {$service->package_name}");
                    $makeupCount++;
                    $totalCreated++;
                } catch (\Exception $e) {
                    $this->error("  ERROR {$service->package_name}: " . $e->getMessage());
                }
            }
        }

        // Initialize Dance Service Snapshots
        $this->info("\nProcessing Dance Services...");
        $dance = DanceService::all();
        $danceCount = 0;

        foreach ($dance as $service) {
            $exists = StockSnapshot::where('service_type', 'tari')
                ->where('service_id', $service->id)
                ->exists();

            if (!$exists) {
                try {
                    StockSnapshot::create([
                        'service_type' => 'tari',
                        'service_id' => $service->id,
                        'service_name' => $service->package_name,
                        'stok_by_admin' => $service->total_slots ?? 0,
                        'stok_from_orders' => 0,
                        'sisa_stok_tersedia' => $service->total_slots ?? 0,
                        'sisa_stok_setelah_booking' => $service->total_slots ?? 0,
                        'admin_history' => json_encode([
                            [
                                'qty' => $service->total_slots ?? 0,
                                'admin_id' => null,
                                'reason' => 'System initialization',
                                'date' => now()
                            ]
                        ])
                    ]);

                    $this->line("  OK {$service->package_name}");
                    $danceCount++;
                    $totalCreated++;
                } catch (\Exception $e) {
                    $this->error("  ERROR {$service->package_name}: " . $e->getMessage());
                }
            }
        }

        // Summary
        $this->info("\n=== SUMMARY ===");
        $this->info("Costumes:       $costumeCount snapshots created");
        $this->info("Makeup:         $makeupCount snapshots created");
        $this->info("Dance Services: $danceCount snapshots created");
        $this->info("Total:          $totalCreated snapshots created\n");

        $this->info("Snapshot initialization complete\n");
    }
}
