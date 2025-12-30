<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\StockSnapshot;
use App\Services\StockSnapshotService;
use Illuminate\Console\Command;

class InitStockSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize stock snapshots from existing services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new StockSnapshotService();
        
        $this->info('=== INITIALIZING STOCK SNAPSHOTS ===');
        
        // Kostum
        $this->info("\nProcessing Kostums...");
        foreach (Costume::all() as $costume) {
            $service->createOrUpdateSnapshot(
                'kostum',
                $costume->id,
                $costume->costume_name,
                $costume->stock,
                null,
                'Initial setup'
            );
            
            $this->line("✓ Kostum: {$costume->costume_name} (Stock: {$costume->stock})");
        }

        // Makeup
        $this->info("\nProcessing Makeup Services...");
        foreach (MakeupService::all() as $makeup) {
            $service->createOrUpdateSnapshot(
                'rias',
                $makeup->id,
                $makeup->package_name,
                $makeup->total_slots,
                null,
                'Initial setup'
            );
            
            $this->line("✓ Makeup: {$makeup->package_name} (Slots: {$makeup->total_slots})");
        }

        // Dance
        $this->info("\nProcessing Dance Services...");
        foreach (DanceService::all() as $dance) {
            $service->createOrUpdateSnapshot(
                'tari',
                $dance->id,
                $dance->package_name,
                $dance->available_slots ?? 0,
                null,
                'Initial setup'
            );
            
            $this->line("✓ Dance: {$dance->package_name} (Slots: " . ($dance->available_slots ?? 0) . ")");
        }

        $this->info("\n=== INITIALIZATION COMPLETE ===");
        $this->info("Total Snapshots: " . StockSnapshot::count());
        
        // Show sample data
        $this->info("\n=== SAMPLE DATA ===");
        $sample = StockSnapshot::first();
        if ($sample) {
            $this->line("Service Type: " . $sample->service_type);
            $this->line("Service ID: " . $sample->service_id);
            $this->line("Service Name: " . $sample->service_name);
            $this->line("Stok by Admin: " . $sample->stok_by_admin);
            $this->line("Stok from Orders: " . $sample->stok_from_orders);
            $this->line("Sisa Stok: " . $sample->sisa_stok_setelah_booking);
            $this->line("Admin History: " . json_encode($sample->admin_history));
        }
    }
}
