<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class RebuildSnapshots extends Command
{
    protected $signature = 'snapshots:rebuild {--force : Skip confirmation}';

    protected $description = 'Rebuild stock snapshots dari awal';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Ini akan menghapus semua snapshots yang ada dan rebuild dari awal. Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        // Hapus semua snapshots lama
        $this->info('🗑️  Menghapus snapshots lama...');
        StockSnapshot::truncate();
        $this->info('✅ Snapshots lama dihapus.');

        $count = 0;

        // Rebuild untuk Costumes
        $this->info('\n📦 Rebuilding Costume snapshots...');
        $costumes = Costume::all();
        foreach ($costumes as $costume) {
            $name = $costume->costume_name ?? $costume->name ?? 'Unknown';
            $stock = $costume->stock ?? 0;
            StockSnapshot::create([
                'service_type' => 'kostum',
                'service_id' => $costume->id,
                'service_name' => $name,
                'stok_by_admin' => $stock,
                'admin_history' => [],
                'stok_from_orders' => 0,
                'sisa_stok_tersedia' => $stock,
                'last_booking_date' => null,
                'sisa_stok_setelah_booking' => $stock,
            ]);
            $count++;
            $this->line("✅ Costume #{$costume->id}: {$name}");
        }

        // Rebuild untuk Dance Services
        $this->info('\n💃 Rebuilding Dance Service snapshots...');
        $danceServices = DanceService::all();
        foreach ($danceServices as $service) {
            $name = $service->package_name ?? $service->name ?? 'Unknown';
            // Dance services tidak punya stock limit, hanya date/time based
            $stock = 0;
            StockSnapshot::create([
                'service_type' => 'tari',
                'service_id' => $service->id,
                'service_name' => $name,
                'stok_by_admin' => $stock,
                'admin_history' => [],
                'stok_from_orders' => 0,
                'sisa_stok_tersedia' => $stock,
                'last_booking_date' => null,
                'sisa_stok_setelah_booking' => $stock,
            ]);
            $count++;
            $this->line("✅ Dance Service #{$service->id}: {$name}");
        }

        // Rebuild untuk Makeup Services
        $this->info('\n💄 Rebuilding Makeup Service snapshots...');
        $makeupServices = MakeupService::all();
        foreach ($makeupServices as $service) {
            $name = $service->package_name ?? $service->service_name ?? 'Unknown';
            // Makeup uses total_slots as stock capacity
            $stock = $service->total_slots ?? 0;
            StockSnapshot::create([
                'service_type' => 'rias',
                'service_id' => $service->id,
                'service_name' => $name,
                'stok_by_admin' => $stock,
                'admin_history' => [],
                'stok_from_orders' => 0,
                'sisa_stok_tersedia' => $stock,
                'last_booking_date' => null,
                'sisa_stok_setelah_booking' => $stock,
            ]);
            $count++;
            $this->line("✅ Makeup Service #{$service->id}: {$name}");
        }

        $this->info("\n🎉 Rebuild selesai! Total snapshots created: {$count}");
        return 0;
    }
}
