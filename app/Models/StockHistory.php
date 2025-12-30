<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_type',
        'service_id',
        'service_name',
        'old_stock',
        'new_stock',
        'change_quantity',
        'change_type',
        'order_id',
        'admin_id',
        'reason'
    ];

    protected $casts = [
        'old_stock' => 'integer',
        'new_stock' => 'integer',
        'change_quantity' => 'integer',
        'created_at' => 'datetime'
    ];

    /**
     * Get related order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Static method to log stock change
     */
    public static function log(
        string $serviceType,
        int $serviceId,
        string $serviceName,
        int $oldStock,
        int $newStock,
        string $changeType,
        ?int $orderId = null,
        ?int $adminId = null,
        ?string $reason = null
    ) {
        return self::create([
            'service_type' => $serviceType,
            'service_id' => $serviceId,
            'service_name' => $serviceName,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'change_quantity' => $newStock - $oldStock,
            'change_type' => $changeType,
            'order_id' => $orderId,
            'admin_id' => $adminId,
            'reason' => $reason
        ]);
    }
}
