<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule to expire unpaid orders every 5 minutes
Schedule::command('orders:expire-unpaid')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule to auto-return deliveries every hour (check orders with end_date < today)
Schedule::command('orders:auto-return')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
