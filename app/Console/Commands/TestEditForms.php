<?php

namespace App\Console\Commands;

use App\Models\Costume;
use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class TestEditForms extends Command
{
    protected $signature = 'test:edit-forms';
    protected $description = 'Test edit forms have snapshot data';

    public function handle()
    {
        $this->info("\n=== Edit Form UI Test ===\n");

        $costume = Costume::first();
        if ($costume) {
            $snapshot = StockSnapshot::where('service_type', 'kostum')
                ->where('service_id', $costume->id)
                ->first();

            $this->line("Costume: {$costume->costume_name}");
            if ($snapshot) {
                $this->line("  Stok Asli (stok_by_admin): {$snapshot->stok_by_admin}");
                $this->line("  Stok Sekarang (sisa_stok_tersedia): {$snapshot->sisa_stok_tersedia}");
                $this->line("  Booked: {$snapshot->stok_from_orders}");
                $this->info("  OK - Edit form will show both fields\n");
            } else {
                $this->error("  ERROR - No snapshot\n");
            }
        }

        $this->info("Edit forms ready for testing at:");
        $this->info("  - /admin/costumes/{id}/edit");
        $this->info("  - /admin/makeup-services/{id}/edit");
        $this->info("  - /admin/dance-services/{id}/edit\n");
    }
}
