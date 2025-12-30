<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costume extends Model
{
    use HasFactory;

    protected $fillable = [
        'costume_name',
        'description',
        'rental_price',
        'stock',
        'size',
        'image_path',
        'is_available',
        'views_count'
    ];

    protected $casts = [
        'rental_price' => 'decimal:2',
        'stock' => 'integer',
        'is_available' => 'boolean'
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
     * Call this whenever someone views the costume
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
     * Get order details for this costume
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'detail_id')->where('service_type', 'kostum');
    }

    /**
     * Get stock logs for this costume
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Check if costume is available
     * Must be enabled AND have stock available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return (bool) $this->is_available && $this->stock > 0;
    }

    /**
     * Admin can manually disable/enable this costume
     * 
     * @param bool $available
     * @return bool
     */
    public function setAvailability(bool $available): bool
    {
        return $this->update(['is_available' => $available]);
    }

    /**
     * Calculate available stock for a date range
     * Costume stock is managed by counting active orders
     * Example: Total 10 units, 5 ordered for date range = 5 available
     * 
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function getAvailableStock(string $startDate, string $endDate): int
    {
        $totalStock = $this->stock;
        
        // Count reserved units for overlapping date ranges
        // Block stock jika:
        // 1. Order dengan status paid/settlement/confirmed/processing/ready
        // 2. Order dengan status 'pending'
        // EXCEPT: Order dengan return_status 'sudah' atau 'terlambat' (sudah dikembalikan)
        $bookedQuantity = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
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
        ->where('service_type', 'kostum')
        ->where('detail_id', $this->id)
        ->sum('quantity');

        return max(0, $totalStock - $bookedQuantity);
    }

    /**
     * Check if requested quantity is available for date range
     * 
     * @param int $requestedQty
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function hasStockForDateRange(int $requestedQty, string $startDate, string $endDate): bool
    {
        return $this->getAvailableStock($startDate, $endDate) >= $requestedQty;
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
        return $this->stock > 0 ? 'Tersedia' : 'Habis';
    }
}
