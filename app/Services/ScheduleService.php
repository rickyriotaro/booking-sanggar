<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Check availability for a specific item in a date range
     * Different logic for different service types:
     * - Costume: Based on stock count
     * - Dance: Based on available slots (auto-unavailable when booked)
     * - Makeup: Based on admin is_available flag (always available unless disabled)
     * 
     * @param string $itemType (costume, dance, makeup)
     * @param int $itemId
     * @param string $startDate
     * @param string $endDate
     * @param int $requestedQty
     * @return array
     */
    public function checkAvailability(
        string $itemType,
        int $itemId,
        string $startDate,
        string $endDate,
        int $requestedQty = 1
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        switch ($itemType) {
            case 'costume':
                return $this->checkCostumeAvailability($itemId, $start, $end, $requestedQty);

            case 'dance':
                return $this->checkDanceAvailability($itemId, $start, $end, $requestedQty);

            case 'makeup':
                return $this->checkMakeupAvailability($itemId, $start, $end, $requestedQty);

            default:
                return [
                    'available' => false,
                    'message' => 'Tipe layanan tidak valid',
                    'total_stock' => 0,
                    'available_stock' => 0,
                ];
        }
    }

    /**
     * Check costume availability
     * Logic: Total stock minus booked quantity
     */
    private function checkCostumeAvailability(int $costumeId, Carbon $start, Carbon $end, int $requestedQty): array
    {
        $costume = Costume::find($costumeId);

        if (!$costume) {
            return [
                'available' => false,
                'message' => 'Kostum tidak ditemukan',
                'total_stock' => 0,
                'available_stock' => 0,
            ];
        }

        $totalStock = $costume->stock;
        $availableStock = $costume->getAvailableStock($start->format('Y-m-d'), $end->format('Y-m-d'));

        return [
            'available' => $availableStock >= $requestedQty,
            'message' => $availableStock >= $requestedQty
                ? "Tersedia {$availableStock} dari {$totalStock} kostum"
                : "Stok tidak cukup. Tersedia {$availableStock}, diminta {$requestedQty}",
            'total_stock' => $totalStock,
            'booked_stock' => $totalStock - $availableStock,
            'available_stock' => $availableStock,
            'requested_qty' => $requestedQty,
            'item_name' => $costume->costume_name,
        ];
    }

    /**
     * Check dance service availability
     * Logic for Jasa Tari with duration-based booking:
     * 1. Service must be enabled (is_available flag = true)
     * 2. Time conflict check is done separately at API level
     * 3. Here we only check if service is enabled
     * 
     * NOTE: Per-minute time conflict checking is done in OrderController::checkTimeConflict()
     * This method only validates that the service exists and is enabled
     */
    private function checkDanceAvailability(int $danceId, Carbon $start, Carbon $end, int $requestedQty): array
    {
        $dance = DanceService::find($danceId);

        if (!$dance) {
            return [
                'available' => false,
                'message' => 'Jasa tari tidak ditemukan',
                'item_name' => 'Unknown',
            ];
        }

        // Check if service is enabled by admin
        if (!$dance->is_available) {
            return [
                'available' => false,
                'message' => 'Jasa tari tidak tersedia saat ini (dimatikan oleh admin)',
                'item_name' => $dance->package_name,
            ];
        }

        // For Jasa Tari, availability is based on per-minute time slots
        // Detailed time conflict checking is done in OrderController::checkTimeConflict()
        return [
            'available' => true,
            'message' => "Jasa tari '{$dance->package_name}' tersedia",
            'item_name' => $dance->package_name,
            'duration_minutes' => $dance->duration_minutes ?? 0,
        ];
    }

    /**
     * Check makeup service availability
     * Logic: Check both is_available flag AND available slots
     * Slots are reserved by pending (non-expired) and paid orders
     */
    private function checkMakeupAvailability(int $makeupId, Carbon $start, Carbon $end, int $requestedQty): array
    {
        $makeup = MakeupService::find($makeupId);

        if (!$makeup) {
            return [
                'available' => false,
                'message' => 'Jasa rias tidak ditemukan',
            ];
        }

        // Check if service is enabled by admin
        if (!$makeup->isAvailable()) {
            return [
                'available' => false,
                'message' => 'Jasa rias tidak tersedia saat ini (dimatikan oleh admin)',
                'status' => 'disabled',
                'item_name' => $makeup->package_name,
            ];
        }

        // Check available slots for date range
        $totalSlots = $makeup->total_slots ?? 1;

        // Count booked slots for overlapping dates
        $bookedSlots = OrderDetail::whereHas('order', function ($query) use ($start, $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
                ->whereIn('status', ['paid', 'settlement', 'confirmed', 'processing', 'ready', 'pending']);
        })
            ->where('service_type', 'rias')
            ->where('detail_id', $makeupId)
            ->sum('quantity');

        $availableSlots = max(0, $totalSlots - $bookedSlots);
        $isAvailable = $availableSlots >= $requestedQty;

        return [
            'available' => $isAvailable,
            'message' => $isAvailable
                ? "Jasa rias tersedia ({$availableSlots} slot tersedia)"
                : "Jasa rias tidak tersedia (hanya {$availableSlots} slot, diminta {$requestedQty})",
            'status' => $isAvailable ? 'active' : 'full',
            'item_name' => $makeup->package_name,
            'available_slots' => $availableSlots,
            'total_slots' => $totalSlots,
        ];
    }

    /**
     * Get all events/bookings for calendar view
     * Returns only orders that are:
     * - Status pembayaran: paid (sudah bayar)
     * - Status pengembalian: belum (belum dikembalikan)
     * - Still ongoing or will happen in the future (end_date >= today)
     */
    public function getCalendarEvents(?string $startDate = null, ?string $endDate = null, ?string $itemType = null): array
    {
        $query = Order::with([
            'user',
            'orderDetails.costume',
            'orderDetails.danceService',
            'orderDetails.makeupService'
        ])
            // Filter by payment status (paid) and return status (belum = not yet returned)
            ->where('status', 'paid')
            ->where('return_status', 'belum');

        // Always filter for ongoing/future bookings (end_date >= today)
        // regardless of the date range provided
        $today = Carbon::now()->startOfDay();
        $query->where('end_date', '>=', $today);

        if ($itemType) {
            // Normalize item type (handle both Indonesian and English names)
            $normalizedType = match($itemType) {
                'kostum' => 'kostum',
                'tari' => 'tari',
                'rias' => 'rias',
                'costume' => 'kostum',
                'dance' => 'tari',
                'makeup' => 'rias',
                default => $itemType
            };
            
            $query->whereHas('orderDetails', function ($q) use ($normalizedType) {
                $q->where('service_type', $normalizedType);
            });
        }

        $orders = $query->orderBy('start_date')->distinct('id')->get();

        $events = [];
        $processedOrderIds = [];
        
        foreach ($orders as $order) {
            // Skip if we've already processed this order
            if (in_array($order->id, $processedOrderIds)) {
                continue;
            }
            $processedOrderIds[] = $order->id;
            
            $items = [];
            foreach ($order->orderDetails as $detail) {
                $serviceName = match ($detail->service_type) {
                    'kostum' => $detail->costume->costume_name ?? 'Kostum Tidak Ditemukan',
                    'tari' => $detail->danceService->package_name ?? 'Jasa Tari Tidak Ditemukan',
                    'rias' => $detail->makeupService->package_name ?? 'Jasa Rias Tidak Ditemukan',
                    default => 'Layanan Tidak Diketahui'
                };

                $items[] = [
                    'type' => $detail->service_type,
                    'name' => $serviceName,
                    'quantity' => $detail->quantity,
                    'price' => (float) $detail->unit_price,
                ];
            }

            $events[] = [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'start_date' => $order->start_date,
                'end_date' => $order->end_date,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'items' => $items,
            ];
        }

        return $events;
    }

    /**
     * Get availability for each day in a month
     * Shows how many slots/stock is available per date
     */
    public function getMonthAvailability(string $itemType, int $itemId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $availability = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');

            // Check availability for single day (one-day booking)
            $dayAvailability = $this->checkAvailability(
                $itemType,
                $itemId,
                $dateStr,
                $dateStr,
                1
            );

            // Prepare day-specific data based on type
            $dayData = [
                'date' => $dateStr,
                'day_name' => $currentDate->format('l'),
                'is_available' => $dayAvailability['available'],
            ];

            // Add type-specific fields
            match ($itemType) {
                'costume' => $dayData += [
                    'total_stock' => $dayAvailability['total_stock'],
                    'available_stock' => $dayAvailability['available_stock'],
                    'booked_stock' => $dayAvailability['booked_stock'],
                ],
                'dance' => $dayData += [
                    'total_slots' => $dayAvailability['total_slots'],
                    'available_slots' => $dayAvailability['available_slots'],
                    'booked_slots' => $dayAvailability['booked_slots'],
                ],
                'makeup' => $dayData += [
                    'status' => $dayAvailability['status'],
                ],
                default => null,
            };

            $availability[$dateStr] = $dayData;
            $currentDate->addDay();
        }

        return $availability;
    }

    /**
     * Validate entire order before creation
     * Check all items in order for availability
     * Handles mapping: kostum→costume, tari→dance, rias→makeup
     */
    public function validateOrder(array $items, string $startDate, string $endDate): array
    {
        $errors = [];
        $allAvailable = true;

        foreach ($items as $item) {
            // Map service_type names: kostum→costume, tari→dance, rias→makeup
            $internalType = match ($item['service_type']) {
                'kostum' => 'costume',
                'tari' => 'dance',
                'rias' => 'makeup',
                default => $item['service_type']
            };

            $check = $this->checkAvailability(
                $internalType,
                $item['detail_id'],
                $startDate,
                $endDate,
                $item['quantity'] ?? 1
            );

            if (!$check['available']) {
                $allAvailable = false;
                $errors[] = [
                    'service_type' => $item['service_type'],
                    'item_id' => $item['detail_id'],
                    'item_name' => $check['item_name'] ?? 'Unknown',
                    'message' => $check['message'],
                ];
            }
        }

        return [
            'valid' => $allAvailable,
            'errors' => $errors,
        ];
    }

    /**
     * Get booked dates for a service
     * Returns dates where service is fully booked (no stock available)
     * Used by Flutter calendar to disable booked dates
     * 
     * @param string $serviceType ('kostum', 'tari', 'rias')
     * @param int|null $detailId (optional, specific item ID)
     * @param int $days (number of days to check, default 365)
     * @return array List of booked dates in format ['YYYY-MM-DD', ...]
     */
    public function getBookedDatesForService(
        string $serviceType,
        ?int $detailId = null,
        int $days = 365
    ): array {
        $bookedDates = [];
        $today = Carbon::today();
        $endDate = $today->clone()->addDays($days);

        // Map external service type names to internal names
        $internalType = match ($serviceType) {
            'kostum' => 'costume',
            'tari' => 'dance',
            'rias' => 'makeup',
            default => $serviceType
        };

        // Check each day in the range
        for ($date = $today->clone(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            if ($internalType === 'costume') {
                // For costume: check if ALL costumes (or specific one) are out of stock
                if ($detailId) {
                    $availableStock = Costume::find($detailId)?->getAvailableStock($dateStr, $dateStr) ?? 0;
                    if ($availableStock <= 0) {
                        $bookedDates[] = $dateStr;
                    }
                } else {
                    // Check if ANY costume is fully booked (for simplicity, check the first available)
                    // This is a fallback when detail_id is not specified
                    continue;
                }
            } elseif ($internalType === 'dance') {
                // For Jasa Tari with duration-based booking:
                // NO dates are "fully booked" - multiple bookings allowed on same date if times don't conflict
                // Time conflict checking is done per-minute in OrderController::checkTimeConflict()
                // So we NEVER add dates to bookedDates for Jasa Tari
                // This allows calendar to show ALL dates as selectable
                // print("📌 Jasa Tari: No date-level blocking, time conflicts checked at API level\n");
            } elseif ($internalType === 'makeup') {
                // For makeup: check if service is available (skip if not available)
                if ($detailId) {
                    $isAvailable = MakeupService::find($detailId)?->is_available ?? true;
                    if (!$isAvailable) {
                        $bookedDates[] = $dateStr;
                        print("📌 Makeup service (ID: {$detailId}) not available on {$dateStr}\n");
                    }
                }
                // Note: Makeup is typically always available if not disabled
            }
        }

        return $bookedDates;
    }

    /**
     * Get all orders for a specific date
     * Returns all orders that have start_date <= date AND end_date >= date
     * 
     * @param string $date (format: YYYY-MM-DD)
     * @return array List of orders with full details
     */
    public function getOrdersByDate(string $date): array
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');

        $orders = Order::with(['user', 'orderDetails', 'transaction'])
            ->where(function ($q) use ($dateStr) {
                $q->whereDate('start_date', '<=', $dateStr)
                    ->whereDate('end_date', '>=', $dateStr);
            })
            ->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];
        foreach ($orders as $order) {
            $items = [];
            $totalPrice = 0;

            foreach ($order->orderDetails as $detail) {
                $serviceName = match ($detail->service_type) {
                    'kostum' => $detail->costume->costume_name ?? 'Kostum Tidak Ditemukan',
                    'tari' => $detail->danceService->package_name ?? 'Jasa Tari Tidak Ditemukan',
                    'rias' => $detail->makeupService->package_name ?? 'Jasa Rias Tidak Ditemukan',
                    default => 'Layanan Tidak Diketahui'
                };

                $detailPrice = ($detail->unit_price ?? 0) * $detail->quantity;
                $totalPrice += $detailPrice;

                $items[] = [
                    'type' => $detail->service_type,
                    'name' => $serviceName,
                    'quantity' => $detail->quantity,
                    'unit_price' => (float) $detail->unit_price,
                    'total_price' => (float) $detailPrice,
                ];
            }

            $statusColor = [
                'pending' => 'yellow',
                'paid' => 'yellow',
                'confirmed' => 'blue',
                'processing' => 'purple',
                'ready' => 'green',
                'completed' => 'gray',
            ];

            $result[] = [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'phone' => $order->user->phone ?? '-',
                'start_date' => $order->start_date,
                'end_date' => $order->end_date,
                'status' => $order->status,
                'status_color' => $statusColor[$order->status] ?? 'gray',
                'return_status' => $order->return_status ?? 'belum',
                'total_price' => (float) $totalPrice,
                'items' => $items,
            ];
        }

        return $result;
    }

    /**
     * Get all unique dates that have orders
     * Only returns dates between start_date and end_date of actual orders
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array List of dates in format ['YYYY-MM-DD', ...]
     */
    public function getDatesWithOrders(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get all orders that overlap with date range
        $orders = Order::where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
                ->where('end_date', '>=', $start);
        })
            ->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready', 'completed'])
            ->select('start_date', 'end_date')
            ->get();

        // Build array of ALL dates between start and end of each order
        $datesArray = [];

        foreach ($orders as $order) {
            $current = Carbon::parse($order->start_date)->startOfDay();
            $orderEnd = Carbon::parse($order->end_date)->startOfDay();

            // Loop through each date from start to end (inclusive)
            while ($current->lte($orderEnd)) {
                $dateStr = $current->format('Y-m-d');

                // Only add if within the requested range
                if ($current->gte($start) && $current->lte($end)) {
                    if (!in_array($dateStr, $datesArray)) {
                        $datesArray[] = $dateStr;
                    }
                }

                $current->addDay();
            }
        }

        sort($datesArray);
        return $datesArray;
    }

    /**
     * Get next available date and time for a service
     * Finds when the service becomes available again after current bookings
     * 
     * @param string $serviceType ('costume' or 'makeup')
     * @param int $serviceId
     * @return array|null Returns array with next_date, next_time, available_quantity or null if always available
     */
    public function getNextAvailability(string $serviceType, int $serviceId): ?array
    {
        // Get all orders for this service that overlap with future dates
        $futureOrders = OrderDetail::where('service_type', $serviceType)
            ->where('detail_id', $serviceId)
            ->whereHas('order', function ($q) {
                $q->where('status', '!=', 'cancelled')
                    ->where('end_date', '>=', now());
            })
            ->with('order:id,start_date,end_date')
            ->orderBy('id', 'desc')
            ->get();

        if ($futureOrders->isEmpty()) {
            // No future orders, service is available now
            return null;
        }

        // Get the latest order's end_date (when service becomes available again)
        $latestOrder = $futureOrders->first();
        $nextAvailableDateTime = Carbon::parse($latestOrder->order->end_date);

        // Add 10 minutes buffer (as per requirement: 14:00 booking ends → 14:10 available)
        $nextAvailableDateTime->addMinutes(10);

        // If it's in the past, service is available now
        if ($nextAvailableDateTime->isPast()) {
            return null;
        }

        // Get available quantity for next available date
        $nextDate = $nextAvailableDateTime->format('Y-m-d');
        $nextTime = $nextAvailableDateTime->format('H:i');

        // Calculate available quantity on that date
        $availableQty = 0;

        if ($serviceType === 'costume') {
            $costume = Costume::find($serviceId);
            if ($costume) {
                $availableQty = $costume->getAvailableStock($nextDate, $nextDate);
            }
        } elseif ($serviceType === 'rias') {
            $makeup = MakeupService::find($serviceId);
            if ($makeup) {
                $availableQty = $makeup->getAvailableSlots($nextDate, $nextDate);
            }
        }

        return [
            'next_available_date' => $nextDate,
            'next_available_time' => $nextTime,
            'next_available_quantity' => max(1, $availableQty), // At least 1 available
        ];
    }
}
