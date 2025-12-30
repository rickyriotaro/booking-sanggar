<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;

class SimpleNotificationService
{
    /**
     * Send notification to user
     * Saves to database and optionally sends HTTP webhook
     */
    public function sendToUser(User $user, string $title, string $message, array $data = []): bool
    {
        try {
            // Save notification to database
            $notification = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'body' => $message,
                'data' => json_encode($data),
                'is_read' => false,
                'type' => $data['type'] ?? 'general'
            ]);

            // Optional: Send webhook if user has webhook URL
            if (!empty($user->webhook_url)) {
                try {
                    Http::timeout(5)->post($user->webhook_url, [
                        'notification_id' => $notification->id,
                        'title' => $title,
                        'body' => $message,
                        'data' => $data,
                        'timestamp' => now()->toIso8601String()
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Webhook failed for user {$user->id}: " . $e->getMessage());
                    // Don't fail the notification if webhook fails
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to multiple users
     */
    public function sendToMultipleUsers(array $users, string $title, string $message, array $data = []): array
    {
        $results = [];
        foreach ($users as $user) {
            $results[$user->id] = $this->sendToUser($user, $title, $message, $data);
        }
        return $results;
    }

    /**
     * Send rental expiry notification
     */
    public function sendRentalExpiryNotification($order): bool
    {
        $user = $order->user;
        if (!$user) {
            return false;
        }

        $rentalEnd = $order->end_date ? \Carbon\Carbon::parse($order->end_date)->format('d M Y H:i') : 'Unknown';

        return $this->sendToUser($user, 'Rental akan habis', "Pesanan Anda akan berakhir pada $rentalEnd", [
            'type' => 'rental_expiry',
            'order_id' => $order->id
        ]);
    }

    /**
     * Send auto return notification
     */
    public function sendAutoReturnNotification($order, string $newStatus): bool
    {
        $user = $order->user;
        if (!$user) {
            return false;
        }

        $statusLabel = match($newStatus) {
            'sudah' => '✅ Sudah Dikembalikan',
            'terlambat' => '⏰ Terlambat Dikembalikan',
            'gagal' => '❌ Gagal',
            default => $newStatus
        };

        return $this->sendToUser($user, "Status Pengembalian: $statusLabel", "Pesanan $order->order_number - Status: $newStatus", [
            'type' => 'return_status_update',
            'order_id' => $order->id,
            'new_status' => $newStatus
        ]);
    }

    /**
     * Send manual return notification
     */
    public function sendManualReturnNotification($order, string $newStatus): bool
    {
        return $this->sendAutoReturnNotification($order, $newStatus);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        try {
            Notification::findOrFail($notificationId)->update(['is_read' => true]);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications(User $user, int $limit = 50)
    {
        return $user->notifications()
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for user
     */
    public function getNotifications(User $user, int $limit = 50)
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
