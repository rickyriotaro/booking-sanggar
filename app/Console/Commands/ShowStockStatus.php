<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\DanceService;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

/**
 * Show current stock status dari semua services
 * 
 * Menampilkan:
 * - stok_by_admin (yang di-set admin)
 * - stok_from_orders (qty yang di-book)
 * - sisa_stok_tersedia (realtime available)
 */
class ShowStockStatus extends Command
{
    protected $signature = 'stock:status';
    protected $description = 'Show real-time stock status for all services';

    public function handle()
    {
        $this->info("\n╔════════════════════════════════════════════════════════════════════╗");
        $this->info("║                    REAL-TIME STOCK STATUS                         ║");
        $this->info("╚════════════════════════════════════════════════════════════════════╝\n");

        // Costumes
        $this->info("\n<fg=blue>━━━ COSTUMES ━━━</>");
        $costumes = Costume::with('orderDetails')->get();
        
        if ($costumes->isEmpty()) {
            $this->warn("  No costumes found");
        } else {
            foreach ($costumes as $costume) {
                $snapshot = StockSnapshot::where('service_type', 'kostum')
                    ->where('service_id', $costume->id)
                    ->first();

                $byAdmin = $snapshot?->stok_by_admin ?? '-';
                $fromOrders = $snapshot?->stok_from_orders ?? '-';
                $available = $snapshot?->sisa_stok_tersedia ?? '-';

                $status = $available === '-' 
                    ? '<fg=red>❌ No Snapshot</>'
                    : ($available <= 3 
                        ? '<fg=red>●</> Critical' 
                        : ($available <= 10 
                            ? '<fg=yellow>●</> Low' 
                            : '<fg=green>●</> Good'));

                $this->line("  {$costume->costume_name}");
                $this->line("    Admin Stock: <fg=cyan>{$byAdmin}</> | Booked: <fg=yellow>{$fromOrders}</> | Available: <fg=green>{$available}</> {$status}");
            }
        }

        // Makeup Services
        $this->info("\n<fg=blue>━━━ MAKEUP SERVICES ━━━</>");
        $makeup = MakeupService::with('orderDetails')->get();
        
        if ($makeup->isEmpty()) {
            $this->warn("  No makeup services found");
        } else {
            foreach ($makeup as $service) {
                $snapshot = StockSnapshot::where('service_type', 'rias')
                    ->where('service_id', $service->id)
                    ->first();

                $byAdmin = $snapshot?->stok_by_admin ?? '-';
                $fromOrders = $snapshot?->stok_from_orders ?? '-';
                $available = $snapshot?->sisa_stok_tersedia ?? '-';

                $status = $available === '-' 
                    ? '<fg=red>❌ No Snapshot</>'
                    : ($available <= 3 
                        ? '<fg=red>●</> Critical' 
                        : ($available <= 10 
                            ? '<fg=yellow>●</> Low' 
                            : '<fg=green>●</> Good'));

                $this->line("  {$service->package_name}");
                $this->line("    Admin Stock: <fg=cyan>{$byAdmin}</> | Booked: <fg=yellow>{$fromOrders}</> | Available: <fg=green>{$available}</> {$status}");
            }
        }

        // Dance Services
        $this->info("\n<fg=blue>━━━ DANCE SERVICES ━━━</>");
        $dance = DanceService::with('orderDetails')->get();
        
        if ($dance->isEmpty()) {
            $this->warn("  No dance services found");
        } else {
            foreach ($dance as $service) {
                $snapshot = StockSnapshot::where('service_type', 'tari')
                    ->where('service_id', $service->id)
                    ->first();

                $byAdmin = $snapshot?->stok_by_admin ?? '-';
                $fromOrders = $snapshot?->stok_from_orders ?? '-';
                $available = $snapshot?->sisa_stok_tersedia ?? '-';

                $status = $available === '-' 
                    ? '<fg=red>❌ No Snapshot</>'
                    : ($available <= 3 
                        ? '<fg=red>●</> Critical' 
                        : ($available <= 10 
                            ? '<fg=yellow>●</> Low' 
                            : '<fg=green>●</> Good'));

                $this->line("  {$service->package_name}");
                $this->line("    Admin Stock: <fg=cyan>{$byAdmin}</> | Booked: <fg=yellow>{$fromOrders}</> | Available: <fg=green>{$available}</> {$status}");
            }
        }

        $this->info("\n<fg=green>✓ Status check complete</>\n");
    }
}
