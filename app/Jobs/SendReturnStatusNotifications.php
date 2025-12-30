<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReturnStatusNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $returnStatus;

    public function __construct(Order $order, string $returnStatus)
    {
        $this->order = $order;
        $this->returnStatus = $returnStatus;
    }

    public function handle(FirebaseNotificationService $notificationService)
    {
        $order = $this->order->load('user', 'orderDetails.item');
        $user = $order->user;

        if (!$user || !$user->fcm_token) {
            return;
        }

        // Get item details
        $firstItem = $order->orderDetails->first();
        $itemName = $firstItem?->item->name ?? 'Jasa Rental';
        $itemType = $firstItem?->item->item_type ?? 'unknown';

        // Determine notification based on return status and item type
        $title = '';
        $message = '';

        if ($this->returnStatus === 'sudah') {
            $title = '✅ Pengembalian Berhasil!';
            $message = "Terima kasih telah mengembalikan {$itemName}. Semoga Anda puas dengan layanan kami!";
        } elseif ($this->returnStatus === 'terlambat') {
            $title = '⏱️ Pengembalian Terlambat';
            $message = "Pengembalian {$itemName} sudah melebihi waktu yang ditentukan. Terima kasih atas pemahaman Anda.";
        }

        if ($title && $message) {
            $data = [
                'type' => 'return_status_update',
                'order_id' => (string) $order->id,
                'return_status' => $this->returnStatus,
                'action' => 'openOrderDetail',
            ];

            $result = $notificationService->sendToUser($user, $title, $message, $data);

            // Log notification
            if ($result) {
                \App\Models\NotificationLog::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'return_status_update',
                    'scheduled_at' => now(),
                    'sent_at' => now(),
                    'is_sent' => true,
                ]);
            }
        }
    }
}
