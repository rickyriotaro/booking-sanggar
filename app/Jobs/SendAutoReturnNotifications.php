<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendAutoReturnNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Auto-return mechanism:
     * - At end_date + end_time, automatically process return for items still marked 'belum'
     * - Set return_status to 'sudah' if on time, or 'terlambat' if overdue
     * - Send notification to user
     * 
     * This runs ONCE at end_date time for each order
     */
    public function handle(FirebaseNotificationService $notificationService)
    {
        $now = Carbon::now();
        
        // Find orders where end_date + end_time is NOW (within this minute)
        // and return_status is still 'belum'
        $expiredOrders = Order::where('status', 'paid')
            ->where('return_status', 'belum')
            ->whereRaw("CONCAT(end_date, ' ', end_time) <= ?", [
                $now->format('Y-m-d H:i:s'),
            ])
            ->whereRaw("CONCAT(end_date, ' ', end_time) > ?", [
                $now->copy()->subMinute()->format('Y-m-d H:i:s'),
            ])
            ->with('user', 'orderDetails.item')
            ->get();

        foreach ($expiredOrders as $order) {
            // Determine if late or on time
            $endDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $order->end_date . ' ' . $order->end_time
            );
            
            // For now, we consider it on-time if exactly at end_time
            // You can adjust this logic if needed (e.g., 15 min grace period)
            $isLate = $now->greaterThan($endDateTime->copy()->addMinutes(0)); // 0 = no grace period
            
            // Auto-update return_status
            $newStatus = $isLate ? 'terlambat' : 'sudah';
            $order->update(['return_status' => $newStatus]);

            // Check if notification already sent (prevent duplicate)
            $alreadySent = DB::table('notification_logs')
                ->where('order_id', $order->id)
                ->where('type', 'auto_return')
                ->where('is_sent', true)
                ->exists();

            if (!$alreadySent) {
                // Send notification for auto-return
                $notificationService->sendAutoReturnNotification($order, $newStatus);
            }
        }
    }
}
