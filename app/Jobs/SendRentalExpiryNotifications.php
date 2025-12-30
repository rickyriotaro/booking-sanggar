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

class SendRentalExpiryNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FirebaseNotificationService $notificationService)
    {
        // Get all orders that will expire in 1 hour
        // 1 hour before end_date, check current return_status and send appropriate notification
        // - For kostum & rias: send status-based notification (belum/sudah/terlambat)
        // - For tari: skip (tari only gets notif on status change)
        //
        // Scenarios:
        // 1. Jam 03:00, status='belum' -> Notif "Pengembalian akan datang"
        // 2. Jam 03:00, status='sudah' -> Notif "Booking berhasil"
        // 3. Jam 03:00, status='terlambat' -> Notif "Booking terlambat"

        $now = Carbon::now();
        $oneHourLater = $now->copy()->addHour();

        // Query orders that expire between now and 1 hour from now
        $expiringOrders = Order::where('status', 'paid')
            ->whereRaw("CONCAT(end_date, ' ', end_time) BETWEEN ? AND ?", [
                $now->format('Y-m-d H:i:s'),
                $oneHourLater->format('Y-m-d H:i:s'),
            ])
            ->with('user', 'orderDetails.item')
            ->get()
            ->filter(function ($order) {
                // Only keep orders that have kostum or rias items (not tari)
                return $order->orderDetails->some(function ($detail) {
                    $serviceType = $detail->service_type;
                    return in_array($serviceType, ['kostum', 'rias']);
                });
            });

        foreach ($expiringOrders as $order) {
            // Check if notification already sent in the last hour (prevent duplicate)
            $alreadySent = DB::table('notification_logs')
                ->where('order_id', $order->id)
                ->where('type', 'rental_expiry')
                ->where('is_sent', true)
                ->where('sent_at', '>=', $now->copy()->subHour())
                ->exists();

            if (!$alreadySent) {
                // Send notification based on CURRENT return_status
                $notificationService->sendRentalExpiryNotificationWithStatus($order);
            }
        }
    }
}

