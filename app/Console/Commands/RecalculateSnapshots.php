<?php

namespace App\Console\Commands;

use App\Models\StockSnapshot;
use Illuminate\Console\Command;

class RecalculateSnapshots extends Command
{
    protected $signature = 'snapshot:recalculate';
    protected $description = 'Recalculate all stock snapshots';

    public function handle()
    {
        $this->info('Recalculating all snapshots...');

        $count = 0;
        StockSnapshot::chunk(100, function($snapshots) use (&$count) {
            foreach ($snapshots as $snapshot) {
                $snapshot->recalculate();
                $this->line("✓ {$snapshot->service_name}");
                $count++;
            }
        });

        $this->info("\nRecalculated {$count} snapshots");
        return 0;
    }
}
