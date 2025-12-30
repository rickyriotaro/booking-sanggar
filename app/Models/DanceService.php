<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanceService extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'dance_type',
        'number_of_dancers',
        'price',
        'duration_minutes',
        'description',
        'image_path',
        'is_available',
        'stock',
        'views_count'
    ];

    protected $casts = [
        'number_of_dancers' => 'integer',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'stock' => 'integer'
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
     * Call this whenever someone views the dance service
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
        return $this->hasMany(OrderDetail::class, 'detail_id')->where('service_type', 'tari');
    }

    /**
     * Get all orders that include this dance service
     */
    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            OrderDetail::class,
            'detail_id',
            'id',
            'id',
            'order_id'
        )->where('order_details.service_type', 'tari');
    }

    /**
     * Check if dance service is available
     * Auto-unavailable if already booked
     */
    public function isAvailable(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        // Check if there are any active bookings
        // Include 'paid' status - order remains active until end_date/expire
        // EXCLUDE: Orders already returned (return_status = 'sudah' or 'terlambat')
        $hasActiveBooking = OrderDetail::whereHas('order', function ($query) {
            $query->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready'])
                // EXCLUDE: Items already returned or terlambat
                ->where(function ($q) {
                    $q->where('return_status', '!=', 'sudah')
                      ->where('return_status', '!=', 'terlambat')
                      ->orWhereNull('return_status');
                });
        })
            ->where('service_type', 'tari')
            ->where('detail_id', $this->id)
            ->exists();

        return !$hasActiveBooking;
    }

    /**
     * Set service as booked (unavailable)
     */
    public function setBooked(): bool
    {
        // Dance service becomes unavailable when booked
        // We don't change is_available flag, just check active bookings
        return true;
    }

    /**
     * Check availability status text
     */
    public function getAvailabilityStatus(): string
    {
        if (!$this->is_available) {
            return 'Tidak Tersedia';
        }

        if (!$this->isAvailable()) {
            return 'Booking';
        }

        return 'Tersedia';
    }

    /**
     * Check if dance service is available for specific dates
     * Dance service is automatically unavailable if dates are booked
     * 
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isAvailableForDates(string $startDate, string $endDate): bool
    {
        // Service must be manually enabled
        if (!$this->is_available) {
            return false;
        }

        // Check if any date in range is already booked
        // Include 'paid' status - order remains active until end_date/expire
        $existingBookings = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
        })
            ->where('service_type', 'tari')
            ->where('detail_id', $this->id)
            ->exists();

        return !$existingBookings;
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

        // Count booked slots in the date range
        // EXCEPT: Orders that are already returned (return_status = 'sudah' or 'terlambat')
        $bookedSlots = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereIn('status', ['pending', 'paid', 'confirmed', 'processing', 'ready'])
                // EXCLUDE: Items already returned or terlambat (already returned)
                ->where(function ($q) {
                    $q->where('return_status', '!=', 'sudah')
                      ->where('return_status', '!=', 'terlambat')
                      ->orWhereNull('return_status');
                })
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
        })
            ->where('service_type', 'tari')
            ->where('detail_id', $this->id)
            ->sum('quantity');

        return max(0, $this->stock - $bookedSlots);
    }
}
