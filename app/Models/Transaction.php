<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_code',
        'payment_method',
        'payment_channel',
        'va_number',
        'account_name',
        'bank_code',
        'instruction_text',
        'amount',
        'pg_status',
        'payment_details',
        'expires_at',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_details' => 'json'
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Format JSON response - ensure numeric fields are numbers
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Ensure amount is numeric
        if (isset($array['amount'])) {
            $array['amount'] = floatval($array['amount']);
        }
        
        return $array;
    }
}
