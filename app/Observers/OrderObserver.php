<?php

namespace App\Observers;

use App\Services\SimpleNotificationService;
use App\Services\FirebaseNotificationService;
use App\Models\Order;
use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\DanceService;
use App\Models\StockSnapshot;

/**
 * Observer to auto-update stock snapshots when order is created/updated
 * Also handles instant notifications for manual return status changes (admin action)
 * 
 * When an order is placed or status changes, all related snapshots are recalculated
 * 
 * Notification flow:
 * - 1 hour before end_date: SendRentalExpiryNotifications job checks status & sends appropriate notif
 * - At end_date: SendAutoReturnNotifications job auto-returns 'belum' orders and sends notif
 * - Manual admin return: This observer sends instant notif (catchall for any manual status changes)
 */
class OrderObserver
{
    /**
     * When order is created - recalculate snapshots for all services in order
     */
    public function created(Order $order): void
    {
        $this->recalculateSnapshots($order, 'Order created');
    }

    /**
     * When order is updated - recalculate if status or return_status changed
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') || $order->isDirty('return_status')) {
            $this->recalculateSnapshots($order, 'Status updated');
            
            // Send notification if return_status changed (manual by admin)
            $this->handleReturnStatusChange($order);
        }
    }

    /**
     * When order is deleted - recalculate snapshots
     */
    public function deleted(Order $order): void
    {
        $this->recalculateSnapshots($order, 'Order deleted');
    }

    /**
     * Handle return status change notifications (MANUAL changes by admin or AUTO changes)
     * Uses both SimpleNotificationService (database) and FirebaseNotificationService (FCM push)
     */
    private function handleReturnStatusChange(Order $order): void
    {
        // Check if return_status changed
        if ($order->isDirty('return_status')) {
            $oldStatus = $order->getOriginal('return_status');
            $newStatus = $order->return_status;

            // Send notification for any manual status change
            // This acts as a catchall for admin manual returns and scheduled auto-returns

            if ($newStatus === 'gagal') {
                // Payment expired - send payment failure notification
                $firebaseService = app(FirebaseNotificationService::class);
                $firebaseService->sendPaymentExpiredNotification($order);
            } elseif ($newStatus === 'sudah' || $newStatus === 'terlambat') {
                // Return status changed - send return notification
                // Send to database AND FCM
                $notificationService = app(SimpleNotificationService::class);
                $notificationService->sendManualReturnNotification($order, $newStatus);
                
                // Also send to FCM for push notification
                $firebaseService = app(FirebaseNotificationService::class);
                $firebaseService->sendManualReturnNotification($order, $newStatus);
            }
        }
    }    /**
     * Recalculate snapshots for all services in this order
     */
    private function recalculateSnapshots(Order $order, string $reason): void
    {
        // Force reload order details from database
        $orderDetails = $order->load('orderDetails')->orderDetails;
        
        if (!$orderDetails || $orderDetails->isEmpty()) {
            return;
        }
        
        foreach ($orderDetails as $detail) {
            $serviceType = $detail->service_type;
            $serviceId = $detail->detail_id;
            
            $snapshot = StockSnapshot::firstOrCreate(
                [
                    'service_type' => $serviceType,
                    'service_id' => $serviceId,
                ],
                [
                    'service_name' => $this->getServiceName($serviceType, $serviceId),
                    'stok_by_admin' => $this->getAdminStock($serviceType, $serviceId),
                ]
            );
            
            $snapshot->recalculate();
            $snapshot->save();
        }
    }

    /**
     * Get service name by type and ID
     */
    private function getServiceName(string $type, int $id): string
    {
        return match($type) {
            'kostum' => Costume::find($id)?->costume_name ?? "Costume $id",
            'rias' => MakeupService::find($id)?->package_name ?? "Makeup $id",
            'tari' => DanceService::find($id)?->package_name ?? "Dance $id",
            default => "Service $id"
        };
    }


    /**
     * Get admin stock by type and ID
     */
    private function getAdminStock(string $type, int $id): int
    {
        return match($type) {
            'kostum' => Costume::find($id)?->stock ?? 0,
            'rias' => MakeupService::find($id)?->total_slots ?? 0,
            'tari' => 0,  // Jasa tari tidak punya stock
            default => 0
        };
    }
}


