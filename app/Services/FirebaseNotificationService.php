<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;

class FirebaseNotificationService
{
    protected $messaging;
    protected $isAvailable = false;

    public function __construct()
    {
        try {
            // Check if Firebase package is installed
            if (!class_exists('Kreait\Firebase\Factory')) {
                \Log::warning('Firebase package not installed. Install with: composer require kreait/firebase-php');
                $this->messaging = null;
                $this->isAvailable = false;
                return;
            }

            $this->isAvailable = true;
            $credentialsPath = config('firebase.credentials_path');
            if (file_exists($credentialsPath)) {
                $factory = new \Kreait\Firebase\Factory();
                $this->messaging = $factory
                    ->withServiceAccount($credentialsPath)
                    ->createMessaging();
            }
        } catch (\Exception $e) {
            \Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->messaging = null;
            $this->isAvailable = false;
        }
    }

    /**
     * Send FCM notification to user
     */
    public function sendToUser(User $user, string $title, string $message, array $data = []): void
    {
        if (!$this->isAvailable || !$user->fcm_token || !$this->messaging) {
            
        }

        try {
            $cloudMessage = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $message))
                ->withData($data);

            $result = $this->messaging->send($cloudMessage);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send FCM notification: ' . $e->getMessage());
            
        }
    }

    /**
     * Send FCM to multiple users
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
    public function sendRentalExpiryNotification($order)
    {
        $user = $order->user;
        if (!$user->fcm_token) {
            return false;
        }

        $itemName = $order->orderDetails->first()->item->name ?? 'Jasa Rental';
        $title = 'Jasa akan berakhir!';
        $message = "{$itemName} akan berakhir dalam 1 jam. Siapkan pengembalian!";
        $data = [
            'type' => 'rental_expiry',
            'order_id' => (string) $order->id,
            'action' => 'openOrderDetail',
        ];

        $result = $this->sendToUser($user, $title, $message, $data);

        // Log notification
        if ($result) {
            NotificationLog::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'title' => $title,
                'message' => $message,
                'type' => 'rental_expiry',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'is_sent' => true,
            ]);
        }

        
    }

    /**
     * Send rental expiry notification based on CURRENT return_status
     * 1 jam sebelum end_date, cek status dan kirim notif sesuai status saat itu
     * 
     * Scenarios:
     * - status='belum' -> "Pengembalian akan datang"
     * - status='sudah' -> "Booking berhasil"
     * - status='terlambat' -> "Booking terlambat"
     */
    public function sendRentalExpiryNotificationWithStatus($order)
    {
        $user = $order->user;
        if (!$user->fcm_token) {
            return false;
        }

        $itemName = $order->orderDetails->first()->item->name ?? 'Jasa Rental';
        $returnStatus = $order->return_status;

        // Determine title and message based on CURRENT return_status
        if ($returnStatus === 'belum') {
            $title = '⏰ Pengembalian akan datang';
            $message = "Siapkan {$itemName} untuk dikembalikan dalam 1 jam";
            $notifType = 'rental_reminder';
        } elseif ($returnStatus === 'sudah') {
            $title = '✅ Booking berhasil';
            $message = "Terima kasih! Pesanan {$itemName} telah berhasil dikembalikan";
            $notifType = 'booking_success';
        } elseif ($returnStatus === 'terlambat') {
            $title = '⚠️ Pengembalian terlambat';
            $message = "Pengembalian {$itemName} terlambat. Hubungi admin untuk info lebih lanjut";
            $notifType = 'booking_late';
        } else {
            // Fallback for unknown status
            $title = 'Jasa akan berakhir!';
            $message = "{$itemName} akan berakhir dalam 1 jam. Siapkan pengembalian!";
            $notifType = 'rental_expiry';
        }

        $data = [
            'type' => $notifType,
            'order_id' => (string) $order->id,
            'return_status' => $returnStatus,
            'action' => 'openOrderDetail',
        ];

        $result = $this->sendToUser($user, $title, $message, $data);

        // Log notification
        if ($result) {
            NotificationLog::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'title' => $title,
                'message' => $message,
                'type' => $notifType,
                'scheduled_at' => now(),
                'sent_at' => now(),
                'is_sent' => true,
            ]);
        }

        
    }

    /**
     * Send payment expired notification
     * Called when payment fails to complete before expiry time
     * 
     * @param $order Order object
     */
    public function sendPaymentExpiredNotification($order)
    {
        $user = $order->user;
        if (!$user->fcm_token) {
            return false;
        }

        $itemName = $order->orderDetails->first()->item->name ?? 'Jasa Rental';

        $title = '❌ Pembayaran Gagal';
        $message = "Pembayaran untuk {$itemName} tidak selesai. Pesanan dibatalkan. Silakan buat pesanan baru.";
        $notifType = 'payment_expired';

        $data = [
            'type' => $notifType,
            'order_id' => (string) $order->id,
            'action' => 'openOrderDetail',
        ];

        $result = $this->sendToUser($user, $title, $message, $data);

        // Log notification
        if ($result) {
            NotificationLog::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'title' => $title,
                'message' => $message,
                'type' => 'payment_expired',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'is_sent' => true,
            ]);
        }

        return $result;
    }

    /**
     * Send auto-return notification (at end_date + end_time)
     * System automatically marks order as returned (sudah) or late (terlambat)
     * 
     * @param $order Order object
     * @param string $newStatus Either 'sudah' or 'terlambat'
     */
    public function sendAutoReturnNotification($order, string $newStatus)
    {
        $user = $order->user;
        if (!$user->fcm_token) {
            return false;
        }

        $itemName = $order->orderDetails->first()->item->name ?? 'Jasa Rental';

        // Determine message based on return status after auto-return
        if ($newStatus === 'sudah') {
            $title = '✅ Booking berhasil';
            $message = "Terima kasih! Pesanan {$itemName} telah berhasil dikembalikan";
            $notifType = 'auto_return_success';
        } elseif ($newStatus === 'terlambat') {
            $title = '⚠️ Pengembalian terlambat';
            $message = "Pengembalian {$itemName} terlambat. Hubungi admin untuk info lebih lanjut";
            $notifType = 'auto_return_late';
        } else {
            $title = 'Order Updated';
            $message = "Status pesanan {$itemName} telah diperbarui";
            $notifType = 'auto_return';
        }

        $data = [
            'type' => $notifType,
            'order_id' => (string) $order->id,
            'return_status' => $newStatus,
            'action' => 'openOrderDetail',
        ];

        $result = $this->sendToUser($user, $title, $message, $data);

        // Log notification
        if ($result) {
            NotificationLog::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'title' => $title,
                'message' => $message,
                'type' => 'auto_return',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'is_sent' => true,
            ]);
        }

        
    }

    /**
     * Send manual return notification (when admin manually changes return_status)
     * Called by OrderObserver when admin changes return_status via dashboard
     * 
     * @param $order Order object
     * @param string $newStatus Either 'sudah' or 'terlambat'
     */
    public function sendManualReturnNotification($order, string $newStatus)
    {
        $user = $order->user;
        if (!$user->fcm_token) {
            return false;
        }

        $itemName = $order->orderDetails->first()->item->name ?? 'Jasa Rental';

        // Determine message based on new status
        if ($newStatus === 'sudah') {
            $title = '✅ Booking berhasil';
            $message = "Terima kasih! Pesanan {$itemName} telah berhasil dikembalikan";
            $notifType = 'manual_return_success';
        } elseif ($newStatus === 'terlambat') {
            $title = '⚠️ Pengembalian terlambat';
            $message = "Pengembalian {$itemName} terlambat. Hubungi admin untuk info lebih lanjut";
            $notifType = 'manual_return_late';
        } else {
            $title = 'Order Updated';
            $message = "Status pesanan {$itemName} telah diperbarui";
            $notifType = 'manual_return';
        }

        $data = [
            'type' => $notifType,
            'order_id' => (string) $order->id,
            'return_status' => $newStatus,
            'action' => 'openOrderDetail',
        ];

        $result = $this->sendToUser($user, $title, $message, $data);

        // Log notification
        if ($result) {
            NotificationLog::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'title' => $title,
                'message' => $message,
                'type' => 'manual_return',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'is_sent' => true,
            ]);
        }

        
    }
}


