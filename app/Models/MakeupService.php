<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakeupService extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'category',
        'price',
        'description',
        'image_path',
        'is_available',
        'views_count',
        'total_slots'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'total_slots' => 'integer'
    ];

    /**
     * Append custom attributes to JSON
     */
    protected $appends = ['image_url'];

    /**
     * Get full image URL - returns relative path for client-side construction
     * Clients (Flutter/Web) can prepend their base URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? '/storage/' . $this->image_path : null;
    }

    /**
     * Increment view count
     * Call this whenever someone views the makeup service
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get view count
     */
    public function getViewsCount(): int
    {
        return $this->views_count ?? 0;
    }

    /**
     * Get order details for this service
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'detail_id')->where('service_type', 'rias');
    }

    /**
     * Check if makeup service is available
     * Makeup service availability is controlled purely by is_available flag
     * Admin can disable it manually for scheduled maintenance or closed days
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return (bool) $this->is_available;
    }

    /**
     * Admin can manually disable/enable this service
     * 
     * @param bool $available
     * @return bool
     */
    public function setAvailability(bool $available): bool
    {
        return $this->update(['is_available' => $available]);
    }

    /**
     * Get availability status as display string
     * 
     * @return string
     */
    public function getAvailabilityStatus(): string
    {
        if (!$this->is_available) {
            return 'Tidak Tersedia';
        }
        return $this->total_slots > 0 ? 'Tersedia' : 'Habis';
    }

    /**
     * Check if makeup service is available for specific dates
     * Checks both is_available flag and available slots for date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $requestedQty (default 1)
     * @return bool
     */
    public function isAvailableForDates(string $startDate, string $endDate, int $requestedQty = 1): bool
    {
        // Service must be manually enabled
        if (!$this->is_available) {
            return false;
        }

        // Check available slots for date range
        $availableSlots = $this->getAvailableSlots($startDate, $endDate);
        
        return $availableSlots >= $requestedQty;
    }

    /**
     * Get available slots for a specific date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function getAvailableSlots(string $startDate, string $endDate): int
    {
        if (!$this->is_available) {
            return 0;
        }

        $totalSlots = $this->total_slots ?? 1;

        // Count booked slots for overlapping dates
        // Block slot jika:
        // 1. Order dengan status sudah bayar (paid, settlement, success, confirmed, processing, ready)
        // 2. Order dengan status 'pending' (pending order sudah reserve slot)
        // EXCEPT: Orders that are already returned (return_status = 'sudah' or 'terlambat')
        $bookedSlots = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                    // Check for any date overlap
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          // Booking spans entire requested period
                          $q2->where('start_date', '<=', $startDate)
                             ->where('end_date', '>=', $endDate);
                      });
                })
                ->whereIn('status', ['paid', 'settlement', 'success', 'confirmed', 'processing', 'ready', 'pending'])
                // EXCLUDE: Items already returned or terlambat (already returned)
                ->where(function ($q) {
                    $q->where('return_status', '!=', 'sudah')
                      ->where('return_status', '!=', 'terlambat')
                      ->orWhereNull('return_status');
                });
        })
        ->where('service_type', 'rias')
        ->where('detail_id', $this->id)
        ->sum('quantity');

        return max(0, $totalSlots - $bookedSlots);
    }
}
