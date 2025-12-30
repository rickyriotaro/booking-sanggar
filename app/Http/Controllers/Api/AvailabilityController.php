<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockSnapshot;
use App\Services\AvailabilityCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    protected $availabilityCalculator;

    public function __construct(AvailabilityCalculatorService $availabilityCalculator)
    {
        $this->availabilityCalculator = $availabilityCalculator;
    }
    /**
     * Get booked dates untuk item tertentu
     * Untuk block dates di Flutter calendar
     * 
     * GET /api/booked-dates/{serviceType}/{serviceId}?month=2025-12
     */
    public function getBookedDates($serviceType, $serviceId, Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        
        // Parse month (format: 2025-12)
        $startOfMonth = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Map service_type dari Flutter ke backend
        $typeMap = [
            'costume' => 'costume',
            'dance_service' => 'tari',
            'makeup_service' => 'rias',
        ];

        $mappedType = $typeMap[$serviceType] ?? $serviceType;

        // Query booked dates
        $bookedDates = OrderDetail::whereHas('order', function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereIn('status', ['pending', 'confirmed', 'processing', 'ready', 'completed'])
                  ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                      $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                            $q2->where('start_date', '<=', $startOfMonth)
                               ->where('end_date', '>=', $endOfMonth);
                        });
                  });
        })
        ->where('service_type', $mappedType)
        ->where('detail_id', $serviceId)
        ->get(['order_id'])
        ->pluck('order_id')
        ->unique();

        // Get all date ranges dari booked orders
        $orders = Order::whereIn('id', $bookedDates)
                       ->get(['start_date', 'end_date']);

        $blockedDates = [];
        foreach ($orders as $order) {
            $start = \Carbon\Carbon::parse($order->start_date);
            $end = \Carbon\Carbon::parse($order->end_date);
            
            // Add all dates dalam range
            while ($start <= $end) {
                $blockedDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'service_type' => $serviceType,
            'service_id' => $serviceId,
            'month' => $month,
            'booked_dates' => array_unique($blockedDates),
            'total_booked_days' => count(array_unique($blockedDates))
        ]);
    }

    /**
     * Check availability untuk date range tertentu
     * POST /api/check-availability
     * 
     * Request:
     * {
     *   "service_type": "costume|dance_service|makeup_service",
     *   "service_id": 1,
     *   "start_date": "2025-12-25",
     *   "end_date": "2025-12-26",
     *   "quantity": 1
     * }
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|in:costume,dance_service,makeup_service',
            'service_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1',
        ]);

        $typeMap = [
            'costume' => 'costume',
            'dance_service' => 'tari',
            'makeup_service' => 'rias',
        ];

        $mappedType = $typeMap[$validated['service_type']];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $serviceId = $validated['service_id'];
        $requestedQty = $validated['quantity'];

        // Get available stock/slots based on service type
        $availableQty = 0;
        $totalQty = 0;
        $bookedQty = 0;

        switch ($mappedType) {
            case 'costume':
                $costume = \App\Models\Costume::find($serviceId);
                if ($costume) {
                    $totalQty = $costume->stock;
                    $availableQty = $costume->getAvailableStock($startDate, $endDate);
                    $bookedQty = $totalQty - $availableQty;
                }
                break;

            case 'tari':
                $dance = \App\Models\DanceService::find($serviceId);
                if ($dance) {
                    $totalQty = $dance->stock;
                    $availableQty = $dance->getAvailableSlots($startDate, $endDate);
                    $bookedQty = $totalQty - $availableQty;
                }
                break;

            case 'rias':
                $makeup = \App\Models\MakeupService::find($serviceId);
                if ($makeup) {
                    $totalQty = $makeup->total_slots;
                    $availableQty = $makeup->getAvailableSlots($startDate, $endDate);
                    $bookedQty = $totalQty - $availableQty;
                }
                break;
        }

        $isAvailable = $availableQty >= $requestedQty;

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'message' => $isAvailable 
                ? "Tersedia {$availableQty} dari {$totalQty}"
                : "Tidak cukup. Tersedia {$availableQty}, diminta {$requestedQty}",
            'service_type' => $validated['service_type'],
            'service_id' => $serviceId,
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'availability' => [
                'total' => $totalQty,
                'available' => $availableQty,
                'booked' => $bookedQty,
                'requested' => $requestedQty,
            ]
        ]);
    }

    /**
     * Get availability summary untuk sebuah service
     * Dengan date-based calculation
     * 
     * GET /api/availability/{serviceType}/{serviceId}?check_date=2025-11-26&required_qty=5
     */
    public function getSummary(string $serviceType, int $serviceId, Request $request)
    {
        // Get snapshot untuk admin_stock
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $checkDate = $request->query('check_date', now()->format('Y-m-d'));
        $requiredQty = (int)$request->query('required_qty', 1);
        $adminStock = $snapshot->stok_by_admin;

        // Get summary
        $summary = $this->availabilityCalculator->getAvailabilitySummary(
            $serviceType,
            $serviceId,
            $adminStock,
            $checkDate
        );

        // Prepare response
        $data = [
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'service_name' => $snapshot->service_name,
                'admin_stock' => $adminStock,
                'available_today' => $summary['available_today'],
                'fully_booked' => $summary['fully_booked'],
                'next_available' => $summary['next_available'],
                'next_returning' => $summary['next_returning'],
            ]
        ];

        // Generate warning message untuk Flutter
        if ($summary['available_today'] >= $requiredQty) {
            // Immediate available
            $data['data']['message'] = "Stok/slot tersedia sebanyak {$summary['available_today']}";
        } elseif ($summary['next_available']) {
            // Will be available later
            $nextDate = $summary['next_available']['date'];
            $nextAvailableQty = $summary['next_available']['available_qty'];
            $data['data']['message'] = "Stok/slot hanya tersedia sebanyak {$summary['available_today']} hari ini. Akan tersedia tgl {$nextDate} sebanyak {$nextAvailableQty}";
        } else {
            // Fully booked or not available at all
            if ($adminStock > 0) {
                $data['data']['message'] = "Stok/slot produk ini sedang penuh. Stok asli hanya tersedia {$adminStock}";
            } else {
                $data['data']['message'] = "Stok/slot produk ini tidak tersedia";
            }
        }

        return response()->json($data);
    }

    /**
     * Get available qty untuk tanggal specific
     * 
     * GET /api/availability/{serviceType}/{serviceId}/on-date?date=2025-11-27
     */
    public function getOnDate(string $serviceType, int $serviceId, Request $request)
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $checkDate = $request->query('date', now()->format('Y-m-d'));
        $available = $this->availabilityCalculator->getAvailableOnDate(
            $serviceType,
            $serviceId,
            $checkDate,
            $snapshot->stok_by_admin
        );

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'check_date' => $checkDate,
                'admin_stock' => $snapshot->stok_by_admin,
                'available_qty' => $available,
            ]
        ]);
    }

    /**
     * Get next available date untuk user yang mau order qty tertentu
     * 
     * GET /api/availability/{serviceType}/{serviceId}/next?required_qty=5
     */
    public function getNextAvailable(string $serviceType, int $serviceId, Request $request)
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $requiredQty = (int)$request->query('required_qty', 1);
        $startDate = $request->query('start_date', now()->format('Y-m-d'));

        $nextAvailable = $this->availabilityCalculator->getNextAvailableDate(
            $serviceType,
            $serviceId,
            $requiredQty,
            $snapshot->stok_by_admin,
            $startDate
        );

        if ($nextAvailable === null) {
            // Immediately available
            $nextAvailable = [
                'date' => now()->format('Y-m-d'),
                'available_qty' => $this->availabilityCalculator->getAvailableOnDate(
                    $serviceType,
                    $serviceId,
                    $startDate,
                    $snapshot->stok_by_admin
                ),
                'days_from_now' => 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'required_qty' => $requiredQty,
                'admin_stock' => $snapshot->stok_by_admin,
                'next_available_date' => $nextAvailable
            ]
        ]);
    }

    /**
     * Get returning dates dan qty
     * 
     * GET /api/availability/{serviceType}/{serviceId}/returning
     */
    public function getReturning(string $serviceType, int $serviceId)
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $nextReturning = $this->availabilityCalculator->getNextReturningDate(
            $serviceType,
            $serviceId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'next_returning_date' => $nextReturning
            ]
        ]);
    }

    /**
     * Get real-time stok tersedia (untuk admin dashboard)
     * 
     * GET /api/availability/{serviceType}/{serviceId}/real-time
     * 
     * Response:
     * {
     *   "stok_by_admin": 10,        // Set by admin (tidak berubah karena order)
     *   "stok_from_orders": 5,       // Total qty dari orders yang active
     *   "sisa_stok_tersedia": 5      // Stok yang tersedia sekarang (10-5)
     * }
     */
    public function getRealTimeStock(string $serviceType, int $serviceId)
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        // Recalculate untuk ensure latest data
        $snapshot->recalculate();

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'service_name' => $snapshot->service_name,
                'stok_by_admin' => $snapshot->stok_by_admin,           // Admin set value
                'stok_from_orders' => $snapshot->stok_from_orders,     // Active bookings
                'sisa_stok_tersedia' => $snapshot->sisa_stok_tersedia, // Available now
                'last_updated' => $snapshot->updated_at,
            ]
        ]);
    }

    /**
     * Get admin history (audit trail)
     * 
     * GET /api/availability/{serviceType}/{serviceId}/history
     */
    public function getAdminHistory(string $serviceType, int $serviceId)
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'service_name' => $snapshot->service_name,
                'admin_history' => $snapshot->admin_history ?? [],
            ]
        ]);
    }
}
