<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Services\ScheduleService;
use App\Services\StockSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private ScheduleService $scheduleService;
    private StockSnapshotService $stockSnapshotService;

    public function __construct(
        ScheduleService $scheduleService,
        StockSnapshotService $stockSnapshotService
    ) {
        $this->scheduleService = $scheduleService;
        $this->stockSnapshotService = $stockSnapshotService;
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            \Log::info('📋 Fetching orders for user: ' . $userId);

            $orders = Order::where('user_id', $userId)
                ->select('id', 'order_code', 'user_id', 'address_id', 'status', 'return_status', 'total_price', 'total_amount', 'notes', 'start_date', 'end_date', 'created_at', 'updated_at')
                ->with([
                    'orderDetails' => function ($query) {
                        $query->select('id', 'order_id', 'service_type', 'detail_id', 'quantity', 'unit_price', 'rental_time', 'service_duration', 'return_time', 'item_start_date', 'item_end_date', 'created_at')
                            ->with([
                                'costume:id,costume_name,size',
                                'danceService:id,package_name,dance_type',
                                'makeupService:id,package_name,category'
                            ]);
                    },
                    'transaction:id,order_id,pg_status,amount,payment_method,created_at',
                    'address:id,user_id,recipient_name,phone_number,full_address,city,province,postal_code,is_primary'
                ])
                ->latest()
                ->paginate(50); // Increased from 10 to 50 per page

            \Log::info('✅ Orders fetched successfully: ' . $orders->total() . ' total orders, page ' . $orders->currentPage());

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Error fetching orders: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with([
            'orderDetails' => function ($query) {
                // ✅ Explicitly select id (primary key) so it's sent in API response
                $query->select('id', 'order_id', 'service_type', 'detail_id', 'quantity', 'unit_price', 'rental_time', 'service_duration', 'return_time', 'item_start_date', 'item_end_date', 'created_at', 'updated_at')
                    ->with([
                        'costume:id,costume_name,size',
                        'danceService:id,package_name,dance_type',
                        'makeupService:id,package_name,category'
                    ]);
            },
            'transaction:id,order_id,pg_status,amount,payment_method,created_at',
            'address:id,user_id,recipient_name,phone_number,full_address,city,province,postal_code,is_primary'
        ])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'address_id' => 'required|integer|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.service_type' => 'required|in:kostum,tari,rias',
            'items.*.detail_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rental_time' => 'nullable|date_format:H:i',
            'items.*.service_duration' => 'nullable|integer|min:1', // Durasi layanan dalam menit (untuk Jasa Tari)
            'items.*.return_time' => 'nullable|date_format:H:i', // Jam pengembalian (untuk Jasa Tari)
            'items.*.item_start_date' => 'nullable|date', // BARU: Per-item start date (optional, dari Flutter)
            'items.*.item_end_date' => 'nullable|date', // BARU: Per-item end date (optional, dari Flutter)
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            // Validate all items using ScheduleService
            // This handles different availability logic for each service type
            $validationResult = $this->scheduleService->validateOrder(
                $validated['items'],
                $validated['start_date'],
                $validated['end_date']
            );

            if (!$validationResult['valid']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa layanan tidak tersedia untuk tanggal yang dipilih',
                    'errors' => $validationResult['errors']
                ], 422);
            }

            // Calculate total amount & prepare order details
            $totalAmount = 0;
            $orderDetailsData = [];

            foreach ($validated['items'] as $item) {
                $unitPrice = 0;

                switch ($item['service_type']) {
                    case 'kostum':
                        $service = Costume::find($item['detail_id']);
                        $unitPrice = $service->rental_price;
                        break;
                    case 'tari':
                        $service = DanceService::find($item['detail_id']);
                        $unitPrice = $service->price;
                        break;
                    case 'rias':
                        $service = MakeupService::find($item['detail_id']);
                        $unitPrice = $service->price;
                        break;
                }

                $totalAmount += $unitPrice * $item['quantity'];

                // KALKULASI TANGGAL PER-ITEM (FITUR BARU)
                // Prioritas: Gunakan per-item dates dari Flutter jika ada, fallback ke order-level dates
                // Setiap item bisa punya tanggal awal dan akhir yang berbeda
                // Contoh: Kostum A booking 28-29, Tari A booking 27, Rias A booking 30-31

                $itemStartDate = $item['item_start_date'] ?? $validated['start_date']; // Gunakan per-item date jika ada, fallback ke order-level
                $itemEndDate = $item['item_end_date'] ?? null; // Per-item end date dari Flutter

                Log::info('📝 Processing item: ' . $item['service_type']);
                Log::info('   item_start_date from request: ' . ($item['item_start_date'] ?? 'null'));
                Log::info('   item_end_date from request: ' . ($item['item_end_date'] ?? 'null'));
                Log::info('   Using itemStartDate: ' . $itemStartDate);

                // Jika item_end_date dari Flutter tidak ada, hitung berdasarkan service_type
                if ($itemEndDate === null) {
                    // Hitung end_date berdasarkan service_type
                    // Tari (Jasa Tari): same day (end_date = start_date)
                    // Kostum/Rias (Rental 24 jam): end_date = start_date + 1
                    if ($item['service_type'] === 'tari') {
                        $itemEndDate = $itemStartDate; // Tari: same day rental
                    } else {
                        // Kostum atau Rias: +1 day (24-hour rental)
                        $itemEndDate = date('Y-m-d', strtotime($itemStartDate . ' +1 day'));
                    }
                }

                Log::info('   Final itemEndDate: ' . $itemEndDate);

                $orderDetailsData[] = [
                    'service_type' => $item['service_type'],
                    'detail_id' => $item['detail_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'rental_time' => $item['rental_time'] ?? null,
                    'service_duration' => $item['service_duration'] ?? null, // Durasi untuk Jasa Tari
                    'return_time' => $item['return_time'] ?? null, // Jam pengembalian untuk Jasa Tari
                    'item_start_date' => $itemStartDate, // Tanggal mulai per-item (dari Flutter atau calculated)
                    'item_end_date' => $itemEndDate // Tanggal akhir per-item (dari Flutter atau calculated)
                ];
            }

            // BARU: Calculate order-level dates sebagai MIN/MAX dari per-item dates
            // Ini untuk overall order tracking, sementara order_details punya dates spesifik
            $allStartDates = array_map(fn($detail) => $detail['item_start_date'], $orderDetailsData);
            $allEndDates = array_map(fn($detail) => $detail['item_end_date'], $orderDetailsData);

            $orderStartDate = min($allStartDates); // Earliest start date
            $orderEndDate = max($allEndDates); // Latest end date

            Log::info('📅 Order-level dates calculated:');
            Log::info('   All item start dates: ' . implode(', ', $allStartDates));
            Log::info('   All item end dates: ' . implode(', ', $allEndDates));
            Log::info('   Order start_date (MIN): ' . $orderStartDate);
            Log::info('   Order end_date (MAX): ' . $orderEndDate);

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                'address_id' => $validated['address_id'],
                'start_date' => $orderStartDate, // MIN dari per-item start dates
                'end_date' => $orderEndDate, // MAX dari per-item end dates
                'total_price' => $totalAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null
            ]);

            // Create order details & reduce stock/slots immediately
            // Stock/slots are reduced when order is created (pending status)
            // This ensures real-time availability updates for Flutter
            foreach ($orderDetailsData as $detail) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'service_type' => $detail['service_type'],
                    'detail_id' => $detail['detail_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'rental_time' => $detail['rental_time'],
                    'service_duration' => $detail['service_duration'], // Durasi untuk Jasa Tari
                    'return_time' => $detail['return_time'], // Jam pengembalian untuk Jasa Tari
                    'item_start_date' => $detail['item_start_date'], // Tanggal mulai per-item (BARU)
                    'item_end_date' => $detail['item_end_date'] // Tanggal akhir per-item (BARU)
                ]);

                // ⚠️ DO NOT reduce stock from Costume/MakeupService table!
                // Stock snapshot will be recalculated automatically
                // The costume.stock is stok_by_admin (set by admin only, not affected by orders)
                // Only snapshots track: stok_from_orders, sisa_stok_tersedia
            }

            // TRIGGER STOCK SNAPSHOT RECALCULATION
            // After all order details created, recalculate snapshots
            foreach ($orderDetailsData as $detail) {
                $serviceName = '';

                switch ($detail['service_type']) {
                    case 'kostum':
                        $service = Costume::find($detail['detail_id']);
                        $serviceName = $service->costume_name ?? '';
                        break;
                    case 'rias':
                        $service = MakeupService::find($detail['detail_id']);
                        $serviceName = $service->package_name ?? '';
                        break;
                    case 'tari':
                        $service = DanceService::find($detail['detail_id']);
                        $serviceName = $service->package_name ?? '';
                        break;
                }

                // Recalculate snapshot untuk service ini
                $this->stockSnapshotService->createOrUpdateSnapshot(
                    $detail['service_type'],
                    $detail['detail_id'],
                    $serviceName,
                    $this->getAdminStock($detail['service_type'], $detail['detail_id']),
                    null,
                    'Auto-recalculate from order'
                );

                Log::info('📊 Stock snapshot recalculated for ' . $detail['service_type'] . ' ID ' . $detail['detail_id']);
            }

            // Create transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'transaction_code' => 'TRX-' . strtoupper(Str::random(10)),
                'amount' => $totalAmount,
                'pg_status' => 'pending',
                'expires_at' => now()->addHours(1)  // Payment expires in 1 hour
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'order' => $order->load(['orderDetails', 'transaction', 'address']),
                    'transaction_code' => $transaction->transaction_code
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Order creation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        if (!in_array($order->status, ['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pesanan pending yang dapat dibatalkan'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Cancel pending order and restore stock/slots
            // Stock/slots are restored because they were reduced when order was created

            foreach ($order->orderDetails as $detail) {
                switch ($detail->service_type) {
                    case 'kostum':
                        $costume = Costume::find($detail->detail_id);
                        if ($costume) {
                            $costume->increment('stock', $detail->quantity);
                            Log::info('🧥 Costume ID ' . $detail->detail_id . ' stock restored by ' . $detail->quantity);
                        }
                        break;
                    case 'rias':
                        $makeup = MakeupService::find($detail->detail_id);
                        if ($makeup) {
                            $makeup->increment('total_slots', $detail->quantity);
                            Log::info('💄 Makeup ID ' . $detail->detail_id . ' slots restored by ' . $detail->quantity);
                        }
                        break;
                    case 'tari':
                        // Dance service doesn't have stock tracking
                        break;
                }
            }

            $order->update(['status' => 'cancelled']);

            // TRIGGER STOCK SNAPSHOT RECALCULATION on cancellation
            foreach ($order->orderDetails as $detail) {
                $serviceName = '';

                switch ($detail->service_type) {
                    case 'kostum':
                        $service = Costume::find($detail->detail_id);
                        $serviceName = $service->costume_name ?? '';
                        break;
                    case 'rias':
                        $service = MakeupService::find($detail->detail_id);
                        $serviceName = $service->package_name ?? '';
                        break;
                    case 'tari':
                        $service = DanceService::find($detail->detail_id);
                        $serviceName = $service->package_name ?? '';
                        break;
                }

                // Recalculate snapshot
                $this->stockSnapshotService->createOrUpdateSnapshot(
                    $detail->service_type,
                    $detail->detail_id,
                    $serviceName,
                    $this->getAdminStock($detail->service_type, $detail->detail_id),
                    null,
                    'Auto-recalculate from order cancellation'
                );

                Log::info('📊 Stock snapshot recalculated on cancellation for ' . $detail->service_type . ' ID ' . $detail->detail_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get admin-set stock from the original service table
     */
    private function getAdminStock(string $serviceType, int $serviceId): int
    {
        return match ($serviceType) {
            'kostum' => Costume::find($serviceId)?->stock ?? 0,
            'rias' => MakeupService::find($serviceId)?->total_slots ?? 0,
            'tari' => DanceService::find($serviceId)?->available_slots ?? 0,
            default => 0
        };
    }

    /**
     * Check for time slot conflicts for Jasa Tari
     * Returns conflict details if there's a booking that overlaps with requested time
     */
    public function checkTimeConflict(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'service_type' => 'required|in:tari,kostum,rias',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        // Only check conflicts for Jasa Tari
        if ($validated['service_type'] !== 'tari') {
            return response()->json([
                'available' => true,
                'message' => 'Tipe layanan ini tidak perlu pengecekan waktu'
            ]);
        }

        $serviceId = $validated['service_id'];
        $date = $validated['date'];
        $startTime = $validated['start_time'];
        $durationMinutes = $validated['duration_minutes'];

        // Parse times
        [$startHour, $startMinute] = explode(':', $startTime);
        $startTotalMinutes = (int)$startHour * 60 + (int)$startMinute;
        $endTotalMinutes = $startTotalMinutes + $durationMinutes;
        $endHour = (int)($endTotalMinutes / 60);
        $endMinute = $endTotalMinutes % 60;
        $endTime = str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($endMinute, 2, '0', STR_PAD_LEFT);

        // Check for overlapping bookings on the same date
        $conflicts = OrderDetail::where('service_type', 'tari')
            ->where('detail_id', $serviceId)
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereDate('orders.start_date', '<=', $date)
            ->whereDate('orders.end_date', '>=', $date)
            ->where('orders.status', '!=', 'cancelled')
            ->select('order_details.rental_time', 'order_details.return_time')
            ->get();

        // Check for time overlaps
        foreach ($conflicts as $conflict) {
            $existingStartTime = $conflict->rental_time; // HH:MM
            $existingEndTime = $conflict->return_time; // HH:MM (auto-calculated)

            if ($existingStartTime && $existingEndTime) {
                [$exStartHour, $exStartMinute] = explode(':', $existingStartTime);
                [$exEndHour, $exEndMinute] = explode(':', $existingEndTime);

                $exStartMinutes = (int)$exStartHour * 60 + (int)$exStartMinute;
                $exEndMinutes = (int)$exEndHour * 60 + (int)$exEndMinute;

                // Check if there's an overlap
                // Conflict if: requestStart < existingEnd AND requestEnd > existingStart
                if ($startTotalMinutes < $exEndMinutes && $endTotalMinutes > $exStartMinutes) {
                    // There's a conflict!
                    return response()->json([
                        'available' => false,
                        'message' => "Waktu jam $startTime bentrok dengan booking yang sudah ada. Booking sebelumnya selesai pada jam $existingEndTime.",
                        'existing_end_time' => $existingEndTime,
                        'suggested_time' => $existingEndTime, // Suggest the end time of conflicting booking
                    ], 422);
                }
            }
        }

        // No conflict found
        return response()->json([
            'available' => true,
            'message' => 'Waktu ini tersedia untuk dipesan'
        ]);
    }
}
