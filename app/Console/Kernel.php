<?php

namespace App\Console;

use App\Jobs\SendRentalExpiryNotifications;
use App\Jobs\SendAutoReturnNotifications;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * 
     * Notification flow:
     * 1. Every minute: SendRentalExpiryNotifications checks if any order expires in next 1 hour
     *    - If yes, check current return_status and send appropriate notification
     *    - For kostum/rias only
     * 
     * 2. Every minute: SendAutoReturnNotifications checks if any order has passed end_date
     *    - If yes, auto-update return_status from 'belum' to 'sudah' or 'terlambat'
     *    - Send notification for the auto-return
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run 1-hour-before reminder every minute
        // Checks orders that expire between now and +1 hour
        // For kostum/rias only
        $schedule->job(new SendRentalExpiryNotifications())
            ->everyMinute()
            ->withoutOverlapping()
            ->name('send-rental-expiry-notifications');

        // Run auto-return processor every minute
        // Checks orders that have passed end_date and auto-returns them
        // Runs for all service types that still have return_status='belum'
        $schedule->job(new SendAutoReturnNotifications())
            ->everyMinute()
            ->withoutOverlapping()
            ->name('send-auto-return-notifications');

        // Optional: Log schedule runs for debugging
        // $schedule->command('schedule:list')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
