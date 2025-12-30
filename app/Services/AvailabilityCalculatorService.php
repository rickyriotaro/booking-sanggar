<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;

/**
 * Service untuk calculate stok/slot availability per tanggal
 * 
 * Contoh:
 * - Stok asli: 10
 * - User A booking tgl 26-27 qty 2
 * - User B booking tgl 27-28 qty 3
 * 
 * Availability:
 * - Tgl 25: 10 (semua available, tidak ada booking)
 * - Tgl 26: 8 (10 - 2 User A)
 * - Tgl 27: 5 (10 - 2 User A - 3 User B) ← most restricted
 * - Tgl 28: 7 (10 - 3 User B) ← User A sudah return
 * - Tgl 29: 10 (semua return)
 */
class AvailabilityCalculatorService
{
    /**
     * Get available qty untuk tanggal tertentu
     * 
     * @param string $serviceType (kostum|rias|tari)
     * @param int $serviceId
     * @param string $checkDate (Y-m-d)
     * @param int $adminStock (stok asli dari admin)
     * @return int available qty untuk tanggal tsb
     */
    public function getAvailableOnDate(
        string $serviceType,
        int $serviceId,
        string $checkDate,
        int $adminStock
    ): int {
        $checkDate = Carbon::parse($checkDate)->startOfDay();
        
        // Query: sum(quantity) dari orders yang overlap dengan checkDate
        // Order overlap jika: start_date <= checkDate AND end_date >= checkDate
        $bookedQty = OrderDetail::where('service_type', $serviceType)
            ->where('detail_id', $serviceId)
            ->whereHas('order', function ($q) use ($checkDate) {
                $q->where('status', '!=', 'cancelled')
                  ->where('return_status', '!=', 'sudah')
                  ->where('return_status', '!=', 'terlambat')
                  ->where('start_date', '<=', $checkDate)
                  ->where('end_date', '>=', $checkDate);
            })
            ->sum('quantity');
        
        return max(0, $adminStock - $bookedQty);
    }

    /**
     * Get next available date dan qty
     * 
     * Gunakan ini untuk warning: "Akan tersedia tgl X sebanyak Y"
     * 
     * @param string $serviceType
     * @param int $serviceId
     * @param int $requiredQty (qty yang user mau booking)
     * @param int $adminStock
     * @param string $startDate (default: today)
     * @return array|null ['date' => '2025-11-27', 'available_qty' => 7] atau null jika immediate available
     */
    public function getNextAvailableDate(
        string $serviceType,
        int $serviceId,
        int $requiredQty,
        int $adminStock,
        ?string $startDate = null
    ): ?array {
        $today = Carbon::parse($startDate ?? now())->startOfDay();
        
        // Check available qty hari ini
        $availableToday = $this->getAvailableOnDate(
            $serviceType,
            $serviceId,
            $today->format('Y-m-d'),
            $adminStock
        );
        
        // Jika cukup hari ini, return null (immediately available)
        if ($availableToday >= $requiredQty) {
            return null;
        }
        
        // Jika tidak cukup, find next available date
        // Loop maksimal 30 hari ke depan
        for ($i = 1; $i <= 30; $i++) {
            $checkDate = $today->clone()->addDays($i);
            $available = $this->getAvailableOnDate(
                $serviceType,
                $serviceId,
                $checkDate->format('Y-m-d'),
                $adminStock
            );
            
            if ($available >= $requiredQty) {
                return [
                    'date' => $checkDate->format('Y-m-d'),
                    'available_qty' => $available,
                    'days_from_now' => $i
                ];
            }
        }
        
        // Tidak tersedia dalam 30 hari
        return null;
    }

    /**
     * Get closest returning date
     * 
     * Gunakan ini untuk: "Stok akan kembali tgl X"
     * 
     * @return array|null ['date' => '2025-11-27', 'returning_qty' => 2, 'reason' => 'User A return']
     */
    public function getNextReturningDate(
        string $serviceType,
        int $serviceId,
        ?string $afterDate = null
    ): ?array {
        $afterDate = Carbon::parse($afterDate ?? now())->startOfDay();
        
        // Get all active orders untuk service ini
        $orders = Order::with('orderDetails')
            ->where('return_status', '!=', 'sudah')
            ->where('return_status', '!=', 'terlambat')
            ->where('end_date', '>', $afterDate)
            ->orderBy('end_date')
            ->get();
        
        $returningDates = [];
        
        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                if ($detail->service_type === $serviceType && $detail->detail_id === $serviceId) {
                    $endDate = Carbon::parse($order->end_date)->format('Y-m-d');
                    
                    if (!isset($returningDates[$endDate])) {
                        $returningDates[$endDate] = 0;
                    }
                    
                    $returningDates[$endDate] += $detail->quantity;
                }
            }
        }
        
        if (empty($returningDates)) {
            return null;
        }
        
        // Get first returning date
        ksort($returningDates);
        $firstDate = key($returningDates);
        $returningQty = $returningDates[$firstDate];
        
        return [
            'date' => $firstDate,
            'returning_qty' => $returningQty,
            'days_from_now' => Carbon::parse($firstDate)->diffInDays($afterDate)
        ];
    }

    /**
     * Get availability summary (untuk Flutter dashboard)
     * 
     * Return semua info yang butuh Flutter untuk show warning
     */
    public function getAvailabilitySummary(
        string $serviceType,
        int $serviceId,
        int $adminStock,
        ?string $forDate = null
    ): array {
        $forDate = $forDate ?? now()->format('Y-m-d');
        
        $availableToday = $this->getAvailableOnDate(
            $serviceType,
            $serviceId,
            $forDate,
            $adminStock
        );
        
        return [
            'admin_stock' => $adminStock,
            'available_today' => $availableToday,
            'fully_booked' => $availableToday === 0,
            'next_available' => $this->getNextAvailableDate(
                $serviceType,
                $serviceId,
                1,
                $adminStock,
                $forDate
            ),
            'next_returning' => $this->getNextReturningDate(
                $serviceType,
                $serviceId,
                $forDate
            )
        ];
    }
}
