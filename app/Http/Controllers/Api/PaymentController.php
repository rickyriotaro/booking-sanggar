<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Get payment methods untuk custom UI
     * GET /api/payment/methods
     */
    public function getPaymentMethods()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => [
                    [
                        'id' => 'va',
                        'name' => 'Transfer Bank',
                        'description' => 'Transfer melalui Virtual Account',
                        'icon' => 'bank',
                        'channels' => [
                            [
                                'code' => 'bca',
                                'name' => 'BCA Virtual Account',
                                'display_name' => 'BCA',
                            ],
                            [
                                'code' => 'bni',
                                'name' => 'BNI Virtual Account',
                                'display_name' => 'BNI',
                            ],
                            [
                                'code' => 'permata',
                                'name' => 'Permata Virtual Account',
                                'display_name' => 'Permata',
                            ],
                        ],
                    ],
                    [
                        'id' => 'ewallet',
                        'name' => 'E-Wallet',
                        'description' => 'Transfer melalui E-Wallet',
                        'icon' => 'wallet',
                        'channels' => [
                            [
                                'code' => 'gopay',
                                'name' => 'GoPay',
                                'display_name' => 'GoPay',
                            ],
                            [
                                'code' => 'ovo',
                                'name' => 'OVO',
                                'display_name' => 'OVO',
                            ],
                            [
                                'code' => 'dana',
                                'name' => 'DANA',
                                'display_name' => 'DANA',
                            ],
                        ],
                    ],
                    [
                        'id' => 'credit_card',
                        'name' => 'Kartu Kredit',
                        'description' => 'Bayar dengan Kartu Kredit',
                        'icon' => 'credit_card',
                    ],
                    [
                        'id' => 'bank_transfer',
                        'name' => 'Transfer Bank Manual',
                        'description' => 'Transfer manual ke rekening kami',
                        'icon' => 'transfer',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Inisiasi pembayaran dengan metode yang dipilih
     * POST /api/payment/initiate
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string', // va, ewallet, credit_card, bank_transfer
            'payment_channel' => 'nullable|string', // bca, bni, gopay, ovo, dana
        ]);

        try {
            $order = Order::with([
                'user',
                'orderDetails' => function ($q) {
                    $q->with(['costume', 'danceService', 'makeupService']);
                }
            ])->findOrFail($request->order_id);

            // Validasi order status
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah diproses',
                ], 400);
            }

            // Validasi total_price
            $amount = $order->total_price > 0 ? $order->total_price : $order->total_amount;
            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total pembayaran tidak valid',
                ], 400);
            }

            // PENTING: Cek apakah transaksi dan snap_token sudah ada dan masih valid
            $existingTransaction = Transaction::where('order_id', $order->id)->first();

            // Jika snap_token sudah ada dan transaksi belum expired, RE-USE token lama
            if (
                $existingTransaction &&
                !empty($order->snap_token) &&
                $existingTransaction->expires_at !== null &&
                now()->lessThan($existingTransaction->expires_at)
            ) {

                // Return existing snap_token tanpa create baru
                // Ini mencegah timer Snap reset ketika user kembali ke halaman pembayaran

                // Decode payment_details jika berbentuk JSON string
                $paymentDetailsData = $existingTransaction->payment_details;
                if (is_string($paymentDetailsData)) {
                    $paymentDetailsData = json_decode($paymentDetailsData, true) ?? [];
                }

                $paymentDetails = $this->formatPaymentDetails(
                    $existingTransaction->payment_method,
                    $existingTransaction->payment_channel,
                    $paymentDetailsData
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran siap diproses (existing token)',
                    'data' => [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code,
                        'transaction_id' => $existingTransaction->id,
                        'amount' => $amount,
                        'payment_method' => $existingTransaction->payment_method,
                        'payment_channel' => $existingTransaction->payment_channel,
                        'va_number' => $existingTransaction->va_number,
                        'account_name' => $existingTransaction->account_name,
                        'instruction_text' => $existingTransaction->instruction_text,
                        'payment_details' => $paymentDetails,
                        'snap_token' => $order->snap_token, // ← RE-USE token lama
                        'expires_at' => $existingTransaction->expires_at,
                    ],
                ]);
            }

            // Build param untuk Midtrans berdasarkan payment method
            $params = $this->buildPaymentParams($order, $amount, $request->payment_method, $request->payment_channel);

            // Create token BARU (hanya jika belum ada atau sudah expired)
            $result = $this->midtransService->createTransaction($order, $params);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi: ' . $result['message'],
                ], 500);
            }

            // Create atau update transaction record dengan data dari Midtrans
            $transaction = Transaction::where('order_id', $order->id)->first();

            $transactionData = [
                'transaction_code' => 'TRX-' . $order->order_code,
                'amount' => $amount,
                'pg_status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_channel' => $request->payment_channel,
                'va_number' => $result['va_number'] ?? null, // ← DARI MIDTRANS
                'account_name' => $result['account_name'] ?? null, // ← DARI MIDTRANS
                'instruction_text' => $result['instruction_text'] ?? null, // ← DARI MIDTRANS
                'bank_code' => $request->payment_channel, // ← DARI REQUEST
                'payment_details' => $result['payment_details'] ?? [], // ← SIMPAN LANGSUNG (Laravel akan auto-encode)
                'expires_at' => now()->addHours(1), // VA valid 1 JAM (BERUBAH DARI 24 JAM)
            ];

            if ($transaction) {
                $transaction->update($transactionData);
            } else {
                $transactionData['order_id'] = $order->id;
                $transaction = Transaction::create($transactionData);
            }

            // PENTING: Update order table dengan snap_token
            // Snap token diperlukan untuk Flutter membuka Snap webview
            $order->update([
                'snap_token' => $result['snap_token'] ?? null,
            ]);

            // Prepare response
            $paymentDetails = $this->formatPaymentDetails($request->payment_method, $request->payment_channel, $result['payment_details'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran siap diproses',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'payment_method' => $request->payment_method,
                    'payment_channel' => $request->payment_channel,
                    'va_number' => $transaction->va_number, // ← DARI DATABASE
                    'account_name' => $transaction->account_name, // ← DARI DATABASE
                    'instruction_text' => $transaction->instruction_text, // ← DARI DATABASE
                    'payment_details' => $paymentDetails,
                    'snap_token' => $result['snap_token'] ?? null,
                    'expires_at' => $transaction->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build payment params berdasarkan metode yang dipilih
     */
    private function buildPaymentParams($order, $amount, $paymentMethod, $paymentChannel = null)
    {
        // PENTING: Set expiry ke 1 jam dari sekarang (Unix timestamp)
        // Ini akan membuat Midtrans timeout sesuai dengan database Laravel
        $expiryTime = now()->addHours(1);

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
            // PENTING: Set expiry time untuk Midtrans Snap (1 jam = 3600 detik)
            // Ini memastikan timer di Snap sesuai dengan database
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s') . ' +0700', // Start time (Jakarta timezone)
                'unit' => 'minute', // Unit bisa 'minute' atau 'hour'
                'duration' => 60, // 60 menit = 1 jam
            ],
        ];

        // Add payment method specific configuration
        if ($paymentMethod === 'va') {
            $params['bank_transfer'] = [
                'bank' => $paymentChannel ?? 'bca',
            ];
        } elseif ($paymentMethod === '-') {
            $params['gopay'] = [];
            $params['shopeepay'] = [];
        }

        return $params;
    }

    /**
     * Format payment details untuk UI
     */
    private function formatPaymentDetails($paymentMethod, $paymentChannel, $details)
    {
        $formatted = [
            'method' => $paymentMethod,
            'channel' => $paymentChannel,
        ];

        // Add specific details based on method
        switch ($paymentMethod) {
            case 'va':
                $formatted['type'] = 'Virtual Account';
                $formatted['bank'] = strtoupper($paymentChannel ?? 'BCA');
                $formatted['instruction'] = 'Lakukan transfer ke nomor rekening virtual yang telah disediakan';
                $formatted['expires_in_minutes'] = 60; // 1 JAM (BERUBAH DARI 1440 = 24 JAM)
                break;
            case 'ewallet':
                $formatted['type'] = 'E-Wallet';
                $formatted['channel_name'] = ucfirst($paymentChannel);
                $formatted['instruction'] = 'Buka aplikasi ' . ucfirst($paymentChannel) . ' dan lakukan pembayaran';
                break;
            case 'credit_card':
                $formatted['type'] = 'Kartu Kredit';
                $formatted['instruction'] = 'Masukkan data kartu kredit Anda dengan aman';
                break;
            case 'bank_transfer':
                $formatted['type'] = 'Transfer Manual';
                $formatted['bank_account'] = '123-456-789'; // TODO: set dari config
                $formatted['bank_name'] = 'BCA';
                $formatted['account_name'] = 'PT Rants Indonesia';
                $formatted['instruction'] = 'Transfer ke rekening bank yang telah tersedia di atas';
                break;
        }

        return $formatted;
    }

    /**
     * Get payment detail untuk halaman checkout
     * GET /api/payment/detail/{orderId}
     */
    public function getPaymentDetail($orderId)
    {
        try {
            $order = Order::with([
                'user',
                'orderDetails' => function ($q) {
                    $q->with(['costume', 'danceService', 'makeupService']);
                },
                'address'
            ])->findOrFail($orderId);

            $transaction = Transaction::where('order_id', $order->id)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi belum dibuat',
                ], 404);
            }

            $amount = $order->total_price > 0 ? $order->total_price : $order->total_amount;

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'status' => $order->status,
                        'start_date' => $order->start_date,
                        'end_date' => $order->end_date,
                        'total_price' => $amount,
                        'address' => $order->address,
                    ],
                    'order_details' => $order->orderDetails->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'service_type' => $detail->service_type,
                            'name' => $this->getItemName($detail),
                            'quantity' => $detail->quantity,
                            'unit_price' => (float) $detail->unit_price,
                            'subtotal' => (float) $detail->unit_price * $detail->quantity,
                            'rental_time' => $detail->rental_time, // ← Jam mulai rental
                            'return_time' => $detail->return_time, // ← Jam selesai rental
                            'service_duration' => $detail->service_duration, // ← Durasi (menit untuk Tari, null untuk Kostum/Rias)
                            'item_start_date' => $detail->item_start_date, // ← BARU: Tanggal mulai per-item
                            'item_end_date' => $detail->item_end_date, // ← BARU: Tanggal akhir per-item
                        ];
                    }),
                    'transaction' => [
                        'id' => $transaction->id,
                        'transaction_code' => $transaction->transaction_code,
                        'amount' => (float) $transaction->amount,
                        'payment_method' => $transaction->payment_method,
                        'payment_channel' => $transaction->payment_channel,
                        'va_number' => $transaction->va_number,
                        'account_name' => $transaction->account_name,
                        'instruction_text' => $transaction->instruction_text,
                        'pg_status' => $transaction->pg_status,
                        'expires_at' => $transaction->expires_at,
                        'paid_at' => $transaction->paid_at,
                        'created_at' => $transaction->created_at, // Issue #3: Waktu transaksi dibuat
                        'payment_details' => $transaction->payment_details,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get payment detail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create payment untuk order (DEPRECATED - gunakan initiatePayment)
     * POST /api/payment/create
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            $order = Order::with([
                'user',
                'orderDetails' => function ($q) {
                    $q->with(['costume', 'danceService', 'makeupService']);
                }
            ])->findOrFail($request->order_id);

            // Validasi order status
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah diproses',
                ], 400);
            }

            // Validasi total_price (fallback ke total_amount jika total_price 0 atau null)
            $amount = $order->total_price > 0 ? $order->total_price : $order->total_amount;
            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total pembayaran tidak valid. Pastikan order memiliki item.',
                ], 400);
            }

            // Check apakah sudah ada transaksi
            $existingTransaction = Transaction::where('order_id', $order->id)->first();
            if ($existingTransaction && $existingTransaction->pg_status == 'settlement') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah dibayar',
                ], 400);
            }

            // Create Snap Token
            $result = $this->midtransService->createTransaction($order);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi: ' . $result['message'],
                ], 500);
            }

            // Ensure snap_token is not null
            if (empty($result['snap_token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat token pembayaran dari Midtrans',
                ], 500);
            }

            // Create atau update transaction record
            // Cek apakah sudah ada transaction untuk order ini
            $transaction = Transaction::where('order_id', $order->id)->first();

            if ($transaction) {
                // Update existing transaction
                $transaction->update([
                    'transaction_code' => 'TRX-' . $order->order_code,
                    'amount' => $amount,
                    'pg_status' => 'pending',
                ]);
            } else {
                // Create new transaction
                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'transaction_code' => 'TRX-' . $order->order_code,
                    'amount' => $amount,
                    'pg_status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token pembayaran berhasil dibuat',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'order_code' => $order->order_code,
                    'total_price' => $amount,
                    'transaction_id' => $transaction->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook handler untuk notifikasi dari Midtrans
     * POST /api/payment/notification
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            Log::info('🔔 Midtrans Webhook Received:', $notification);

            // Verify webhook signature for security
            // Midtrans signature validation: sha512(order_id + status + gross_amount + server_key)
            if (!$this->verifyMidtransSignature($notification)) {
                Log::warning('⚠️ Webhook signature verification failed', $notification);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $parsedNotification = $this->midtransService->handleNotification((object) $notification);
            Log::info('📊 Parsed Notification:', $parsedNotification);

            // Find order by order_code WITH orderDetails loaded
            $order = Order::with('orderDetails')->where('order_code', $parsedNotification['order_id'])->first();

            if (!$order) {
                Log::error('❌ Order not found for order_code: ' . $parsedNotification['order_id']);
                // Try to find by order ID as fallback
                $order = Order::with('orderDetails')->find($parsedNotification['order_id']);
                if (!$order) {
                    Log::error('❌ Also not found by order ID');
                    return response()->json(['message' => 'Order not found'], 404);
                }
            }

            Log::info('✅ Order found: ' . $order->id . ', Status: ' . $order->status);
            Log::info('📦 OrderDetails count: ' . $order->orderDetails->count());

            // Update transaction
            $transaction = Transaction::where('order_id', $order->id)->first();

            if ($transaction) {
                $transaction->update([
                    'pg_status' => $parsedNotification['status'],
                    'payment_method' => $parsedNotification['payment_type'],
                    'va_number' => $parsedNotification['va_number'],
                    'paid_at' => $parsedNotification['status'] == 'settlement' ? now() : null,
                ]);
                Log::info('✅ Transaction updated: ' . $transaction->id . ', Status: ' . $parsedNotification['status']);
            }

            // Update order status based on payment status
            if ($parsedNotification['status'] == 'settlement') {
                // Payment successful - order is paid, stock/slots are now permanent
                $order->update(['status' => 'paid']);
                Log::info('💰 Order ' . $order->id . ' marked as PAID');

                // REDUCE stock/slots permanently
                Log::info('🔄 Starting stock/slot reduction for order ' . $order->id);
                $this->reduceStockForOrder($order);
                Log::info('✅ Stock/slot reduction completed for order ' . $order->id);
            } elseif (
                $parsedNotification['status'] == 'expire' ||
                $parsedNotification['status'] == 'deny' ||
                $parsedNotification['status'] == 'cancel'
            ) {
                // Payment failed/expired - cancel order and restore stock/slots
                $order->update(['status' => 'cancelled']);
                Log::info('❌ Order ' . $order->id . ' cancelled (status: ' . $parsedNotification['status'] . ')');
            }

            return response()->json(['message' => 'Notification handled successfully']);
        } catch (\Exception $e) {
            Log::error('Notification handling error: ' . $e->getMessage());
            return response()->json(['message' => 'Error handling notification'], 500);
        }
    }

    /**
     * Check payment status
     * GET /api/payment/status/{orderId}
     */
    public function checkStatus($orderId)
    {
        try {
            $order = Order::with('orderDetails')->where('order_code', $orderId)
                ->orWhere('id', $orderId)
                ->firstOrFail();

            $transaction = Transaction::where('order_id', $order->id)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            // Check ke Midtrans
            $result = $this->midtransService->checkTransactionStatus($order->order_code);

            if ($result['success']) {
                $parsedNotification = $this->midtransService->handleNotification($result['data']);

                // Update transaction
                $transaction->update([
                    'pg_status' => $parsedNotification['status'],
                    'payment_method' => $parsedNotification['payment_type'],
                    'paid_at' => $parsedNotification['status'] == 'settlement' ? now() : null,
                ]);

                // Update order status
                if ($parsedNotification['status'] == 'settlement') {
                    $order->update(['status' => 'paid']);
                    Log::info('Order ' . $order->id . ' marked as paid after status check');

                    // REDUCE stock/slots permanently
                    $this->reduceStockForOrder($order);
                } elseif (
                    $parsedNotification['status'] == 'expire' ||
                    $parsedNotification['status'] == 'deny' ||
                    $parsedNotification['status'] == 'cancel'
                ) {
                    $order->update(['status' => 'cancelled']);
                    Log::info('Order ' . $order->id . ' cancelled after status check, stock will be released');
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_status' => $order->status,
                    'payment_status' => $transaction->pg_status,
                    'payment_method' => $transaction->payment_method,
                    'amount' => $transaction->amount,
                    'paid_at' => $transaction->paid_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Check status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
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
     * Handle payment settlement
     * 
     * Stock/slots are already reduced when order was created (pending status).
     * Payment settlement just confirms the order status change.
     * If payment fails/expires, stock will be restored when order is cancelled.
     */
    private function reduceStockForOrder(Order $order)
    {
        // No action needed - stock/slots already reduced when order was created
        Log::info('📊 Order ' . $order->order_code . ' payment settled. Order status updated to paid.');
    }

    /**
     * Restore stock/slots when item is returned
     * Called from Admin OrderController when admin marks item as returned
     * Static method - can be called without instantiation
     * 
     * @param object $detail The order detail item being returned
     */
    public static function restoreStockForOrderItem($detail)
    {
        try {
            // Only restore if order was actually paid (status = 'paid')
            if ($detail->order->status !== 'paid') {
                Log::info('⚠️ Cannot restore stock for order ' . $detail->order->order_code . ' - status is ' . $detail->order->status);
                return;
            }

            Log::info('🔄 Starting stock/slot restoration for item ' . $detail->id);

            switch ($detail->service_type) {
                case 'kostum':
                    // Restore costume stock
                    $costume = Costume::find($detail->detail_id);
                    if ($costume) {
                        $costume->increment('stock', $detail->quantity);
                        Log::info('✅ Costume stock restored: +' . $detail->quantity . ' for ' . $costume->costume_name);
                    }
                    break;

                case 'rias':
                    // Restore makeup slots
                    $makeupService = MakeupService::find($detail->detail_id);
                    if ($makeupService) {
                        $makeupService->increment('total_slots', $detail->quantity);
                        Log::info('✅ Makeup slots restored: +' . $detail->quantity . ' for ' . $makeupService->package_name);
                    }
                    break;

                case 'tari':
                    // Dance service: no stock to restore, just log
                    Log::info('ℹ️ Dance service item returned (no stock to restore)');
                    break;

                default:
                    Log::warning('⚠️ Unknown service type: ' . $detail->service_type);
            }

            Log::info('✅ Stock/slot restoration completed for item ' . $detail->id);
        } catch (\Exception $e) {
            Log::error('❌ Stock restoration failed for item ' . $detail->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Midtrans webhook signature
     * 
     * Midtrans sends signature_key calculated as:
     * SHA512(order_id + status_code + gross_amount + server_key)
     */
    private function verifyMidtransSignature($notification)
    {
        // Get signature_key dari notification
        $signatureKey = $notification['signature_key'] ?? null;
        if (!$signatureKey) {
            Log::warning('❌ No signature_key in notification');
            return false;
        }

        // Get Midtrans server key dari config
        $serverKey = config('midtrans.server_key');
        if (!$serverKey) {
            Log::error('❌ Midtrans server_key not configured');
            return false;
        }

        // Extract required fields for signature verification
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';

        // Calculate expected signature
        // Format: order_id + status_code + gross_amount + server_key
        $signatureInput = $orderId . $statusCode . $grossAmount . $serverKey;
        $expectedSignature = hash('sha512', $signatureInput);

        // Compare signatures
        if ($signatureKey === $expectedSignature) {
            Log::info('✅ Webhook signature verified successfully');
            return true;
        }

        Log::error('❌ Signature verification failed', [
            'expected' => $expectedSignature,
            'received' => $signatureKey,
        ]);
        return false;
    }
}
