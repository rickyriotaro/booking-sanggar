<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_code',
        'start_date',
        'end_date',
        'total_price',
        'total_amount',
        'status',
        'return_status',
        'actual_return_date',
        'snap_token',
        'payment_method',
        'notes',
        'expiry_time'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_return_date' => 'datetime',
        'total_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expiry_time' => 'datetime'
    ];

    /**
     * Get the user who made the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for this order
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get order details
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Get transaction for this order
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Get review for this order
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get stock logs related to this order
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Check if order has rental items (kostum or rias)
     * Used for categorizing booking vs payment orders
     */
    public function getHasRentalItemsAttribute()
    {
        return $this->orderDetails()
            ->whereIn('service_type', ['kostum', 'rias'])
            ->exists();
    }

    /**
     * Format JSON response - ensure numeric fields are numbers
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Ensure total_price and total_amount are numeric
        if (isset($array['total_price'])) {
            $array['total_price'] = floatval($array['total_price']);
        }
        if (isset($array['total_amount'])) {
            $array['total_amount'] = floatval($array['total_amount']);
        }
        
        // Add has_rental_items flag for Flutter categorization
        $array['has_rental_items'] = $this->has_rental_items;

        return $array;
    }
}
