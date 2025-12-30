<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakeupSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'makeup_service_id',
        'total_slots',
        'available_slots',
        'slot_date',
        'slot_time'
    ];

    protected $casts = [
        'slot_date' => 'date',
        'slot_time' => 'datetime:H:i',
        'total_slots' => 'integer',
        'available_slots' => 'integer'
    ];

    /**
     * Get the makeup service that owns this slot
     */
    public function makeupService()
    {
        return $this->belongsTo(MakeupService::class);
    }

    /**
     * Get order details for this slot
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'slot_id')->where('service_type', 'rias');
    }

    /**
     * Check if slot is available
     */
    public function isAvailable(): bool
    {
        return $this->available_slots > 0;
    }

    /**
     * Book slots (reduce available_slots)
     */
    public function bookSlots(int $quantity): bool
    {
        if ($this->available_slots >= $quantity) {
            $this->decrement('available_slots', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Cancel booking (restore available_slots)
     */
    public function cancelBooking(int $quantity): bool
    {
        $newAvailable = $this->available_slots + $quantity;
        if ($newAvailable <= $this->total_slots) {
            $this->increment('available_slots', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Get availability status
     */
    public function getAvailabilityStatus(): string
    {
        if ($this->available_slots <= 0) {
            return 'Penuh';
        }
        
        return $this->available_slots . '/' . $this->total_slots . ' tersedia';
    }
}