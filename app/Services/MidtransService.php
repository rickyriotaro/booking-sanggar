<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap Token untuk pembayaran
     * Return: snap_token, payment info
     * 
     * PENTING: VA number ASLI akan didapat dari webhook Midtrans
     * setelah user klik "Bayar" di Snap dan pilih bank.
     * 
     * Flow:
     * 1. createTransaction() → snap_token ✓
     * 2. User klik "Bayar" di Flutter → Buka Snap ✓
     * 3. User pilih bank di Snap → Midtrans generate VA ✓
     * 4. User transfer → Midtrans kirim webhook ✓
     * 5. handleNotification() → Update DB dengan VA asli ✓
     */
    public function createTransaction($order, $customParams = null)
    {
        // Use total_amount jika total_price 0 atau null
        $amount = ($order->total_price > 0) ? $order->total_price : $order->total_amount;

        // Gunakan custom params jika diberikan, atau generate default
        if ($customParams) {
            $params = $customParams;
        } else {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_code,
                    'gross_amount' => (int) $amount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone_number,
                ],
                'item_details' => $this->getItemDetails($order),
            ];
        }

        try {
            // Get Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);
            
            // Determine payment info berdasarkan payment method yang dipilih
            $accountName = 'PT RANTS';
            $instructionText = null;
            $paymentDetails = null;
            $bankName = null;
            
            if (isset($params['bank_transfer'])) {
                $bankName = $params['bank_transfer']['bank'] ?? 'bca';
                $instructionText = "Klik tombol 'Bayar' untuk melanjutkan ke Snap Midtrans. "
                    . "Setelah memilih bank " . strtoupper($bankName) . ", "
                    . "nomor VA akan ditampilkan di Snap. "
                    . "Gunakan nomor VA tersebut untuk transfer.";
                $paymentDetails = $params['bank_transfer'];
            } elseif (isset($params['gopay'])) {
                $bankName = 'gopay';
                $instructionText = "Buka aplikasi GoPay dan lakukan pembayaran";
                $paymentDetails = $params['gopay'] ?? null;
            } elseif (isset($params['shopeepay'])) {
                $bankName = 'shopeepay';
                $instructionText = "Buka aplikasi ShopeePay dan lakukan pembayaran";
                $paymentDetails = $params['shopeepay'] ?? null;
            } elseif (isset($params['credit_card'])) {
                $bankName = 'credit_card';
                $instructionText = "Masukkan detail kartu kredit Anda";
                $paymentDetails = $params['credit_card'] ?? null;
            } else {
                $instructionText = "Lakukan pembayaran sesuai metode yang dipilih";
            }

            // Return snap token dan informasi pembayaran
            // VA asli akan diterima via webhook dari Midtrans setelah user membuat transaction
            return [
                'success' => true,
                'snap_token' => $snapToken,
                'va_number' => null, // VA ASLI akan diterima dari webhook nanti
                'account_name' => $accountName,
                'bank_name' => $bankName,
                'instruction_text' => $instructionText,
                'payment_details' => $paymentDetails,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans createTransaction error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction details dari Midtrans (untuk fetch VA asli)
     * Digunakan oleh getPaymentDetail endpoint
     */
    public function getTransactionDetails($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            
            $vaNumber = null;
            $bankName = null;
            $accountName = null;
            
            // Safely get VA number (may not exist for all payment types like QRIS, e-wallet)
            if (isset($status->va_numbers) && is_array($status->va_numbers) && count($status->va_numbers) > 0) {
                $vaNumber = $status->va_numbers[0]->va_number ?? null;
                $bankName = $status->va_numbers[0]->bank ?? null;
            }
            
            if (isset($status->receiver_name)) {
                $accountName = $status->receiver_name;
            }
            
            return [
                'success' => true,
                'transaction_id' => $status->transaction_id ?? null,
                'va_number' => $vaNumber,
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'transaction_status' => $status->transaction_status ?? null,
                'settlement_time' => $status->settlement_time ?? null,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Get transaction details error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get item details dari order
     */
    private function getItemDetails($order)
    {
        $items = [];

        foreach ($order->orderDetails as $detail) {
            $items[] = [
                'id' => $detail->id,
                'price' => (int) $detail->unit_price,
                'quantity' => $detail->quantity,
                'name' => $this->getItemName($detail),
            ];
        }

        return $items;
    }

    /**
     * Get nama item berdasarkan service type
     */
    private function getItemName($detail)
    {
        switch ($detail->service_type) {
            case 'kostum':
                $costume = $detail->costume;
                return 'Sewa Kostum - ' . ($costume ? $costume->costume_name : 'Unknown');
            case 'tari':
                $danceService = $detail->danceService;
                return 'Jasa Tari - ' . ($danceService ? $danceService->package_name : 'Unknown');
            case 'rias':
                $makeupService = $detail->makeupService;
                return 'Jasa Rias - ' . ($makeupService ? $makeupService->package_name : 'Unknown');
            default:
                return 'Item';
        }
    }

    /**
     * Check status transaksi dari Midtrans
     */
    public function checkTransactionStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);
            return [
                'success' => true,
                'data' => $status,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse notifikasi dari Midtrans
     */
    public function handleNotification($notification)
    {
        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentType = $notification->payment_type;

        $status = 'pending';

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $status = 'settlement';
            }
        } elseif ($transactionStatus == 'settlement') {
            $status = 'settlement';
        } elseif ($transactionStatus == 'pending') {
            $status = 'pending';
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $status = 'expire';
        }

        // Safely get VA number (may not exist for all payment types)
        $vaNumber = null;
        if (isset($notification->va_numbers) && is_array($notification->va_numbers) && count($notification->va_numbers) > 0) {
            $vaNumber = $notification->va_numbers[0]->va_number ?? null;
        }

        return [
            'order_id' => $orderId,
            'status' => $status,
            'payment_type' => $paymentType,
            'transaction_time' => $notification->transaction_time ?? null,
            'va_number' => $vaNumber,
        ];
    }
}
