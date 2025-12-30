<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_type',
        'detail_id',
        'quantity',
        'unit_price',
        'rental_time',
        'service_duration', // Durasi layanan dalam menit (untuk Jasa Tari)
        'return_time', // Jam pengembalian (untuk Jasa Tari)
        'item_start_date', // Tanggal mulai rental per-item
        'item_end_date', // Tanggal berakhir rental per-item
        'item_return_date', // Tanggal sebenarnya item dikembalikan
        'item_return_status' // Status pengembalian per-item (belum|sudah|terlambat)
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2'
    ];

    /**
     * Append custom attributes to JSON response
     */
    protected $appends = [
        'product_name',
        'service_category',
        'product_details' // Size for costume, dance_type for dance, category for makeup
    ];

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get costume detail
     */
    public function costume()
    {
        return $this->belongsTo(Costume::class, 'detail_id');
    }

    /**
     * Get dance service detail
     */
    public function danceService()
    {
        return $this->belongsTo(DanceService::class, 'detail_id');
    }

    /**
     * Get makeup service detail
     */
    public function makeupService()
    {
        return $this->belongsTo(MakeupService::class, 'detail_id');
    }

    /**
     * Get product name accessor
     */
    public function getProductNameAttribute()
    {
        switch ($this->service_type) {
            case 'kostum':
                // Try to use relationship if already loaded, otherwise fetch
                if ($this->relationLoaded('costume') && $this->costume) {
                    return $this->costume->costume_name ?? ('Kostum #' . $this->detail_id);
                }
                $costume = Costume::find($this->detail_id);
                return $costume ? $costume->costume_name : 'Kostum #' . $this->detail_id;
            case 'tari':
                // Try to use relationship if already loaded, otherwise fetch
                if ($this->relationLoaded('danceService') && $this->danceService) {
                    return $this->danceService->package_name ?? ('Tari #' . $this->detail_id);
                }
                $dance = DanceService::find($this->detail_id);
                return $dance ? $dance->package_name : 'Tari #' . $this->detail_id;
            case 'rias':
                // Try to use relationship if already loaded, otherwise fetch
                if ($this->relationLoaded('makeupService') && $this->makeupService) {
                    return $this->makeupService->package_name ?? ('Rias #' . $this->detail_id);
                }
                $makeup = MakeupService::find($this->detail_id);
                return $makeup ? $makeup->package_name : 'Rias #' . $this->detail_id;
            default:
                return 'Unknown Service';
        }
    }

    /**
     * Get service category name
     */
    public function getServiceCategoryAttribute()
    {
        switch ($this->service_type) {
            case 'kostum':
                return 'Kostum';
            case 'tari':
                return 'Jasa Tari';
            case 'rias':
                return 'Jasa Rias';
            default:
                return ucfirst($this->service_type);
        }
    }

    /**
     * Get product details (size for costume, dance_type for dance, category for makeup)
     */
    public function getProductDetailsAttribute()
    {
        switch ($this->service_type) {
            case 'kostum':
                if ($this->relationLoaded('costume') && $this->costume) {
                    return $this->costume->size;
                }
                $costume = Costume::find($this->detail_id);
                return $costume ? $costume->size : null;
            case 'tari':
                if ($this->relationLoaded('danceService') && $this->danceService) {
                    return $this->danceService->dance_type;
                }
                $dance = DanceService::find($this->detail_id);
                return $dance ? $dance->dance_type : null;
            case 'rias':
                if ($this->relationLoaded('makeupService') && $this->makeupService) {
                    return $this->makeupService->category;
                }
                $makeup = MakeupService::find($this->detail_id);
                return $makeup ? $makeup->category : null;
            default:
                return null;
        }
    }

    /**
     * Format JSON response - ensure numeric fields are numbers
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Ensure unit_price is numeric
        if (isset($array['unit_price'])) {
            $array['unit_price'] = floatval($array['unit_price']);
        }

        // Add product name, service category, and product details
        $array['product_name'] = $this->product_name;
        $array['service_category'] = $this->service_category;
        $array['product_details'] = $this->product_details;

        return $array;
    }

    /**
     * Get the related service (polymorphic)
     */
    public function service()
    {
        switch ($this->service_type) {
            case 'kostum':
                return $this->belongsTo(Costume::class, 'detail_id');
            case 'tari':
                return $this->belongsTo(DanceService::class, 'detail_id');
            case 'rias':
                return $this->belongsTo(MakeupService::class, 'detail_id');
            default:
                return null;
        }
    }

    /**
     * Get service details dynamically
     */
    public function getServiceAttribute()
    {
        switch ($this->service_type) {
            case 'kostum':
                return Costume::find($this->detail_id);
            case 'tari':
                return DanceService::find($this->detail_id);
            case 'rias':
                return MakeupService::find($this->detail_id);
            default:
                return null;
        }
    }
}
