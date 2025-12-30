<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasFactory;

    protected $table = 'stock_log';

    protected $fillable = [
        'costume_id',
        'order_id',
        'quantity_change',
        'log_date',
        'type'
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'log_date' => 'date'
    ];

    /**
     * Get the costume
     */
    public function costume()
    {
        return $this->belongsTo(Costume::class);
    }

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
