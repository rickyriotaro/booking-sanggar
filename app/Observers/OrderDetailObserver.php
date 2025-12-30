<?php

namespace App\Observers;

use App\Models\OrderDetail;
use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\StockSnapshot;

/**
 * Observer untuk auto-update stock snapshot saat order detail ditambahkan/diubah
 */
class OrderDetailObserver
{
    /**
     * When order detail is created - recalculate snapshot
     */
    public function created(OrderDetail $detail): void
    {
        $this->recalculateSnapshot($detail);
    }

    /**
     * When order detail is updated - recalculate snapshot
     */
    public function updated(OrderDetail $detail): void
    {
        // Recalculate if quantity changed
        if ($detail->isDirty('quantity')) {
            $this->recalculateSnapshot($detail);
        }
    }

    /**
     * When order detail is deleted - recalculate snapshot
     */
    public function deleted(OrderDetail $detail): void
    {
        $this->recalculateSnapshot($detail);
    }

    /**
     * Recalculate snapshot for this service
     */
    private function recalculateSnapshot(OrderDetail $detail): void
    {
        $serviceType = $detail->service_type;
        $serviceId = $detail->detail_id;
        
        // Skip jasa tari - it doesn't use stock/slot tracking
        if ($serviceType === 'tari') {
            return;
        }
        
        $snapshot = StockSnapshot::firstOrCreate(
            [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
            ],
            [
                'service_name' => $detail->service_name,
                'stok_by_admin' => $this->getAdminStock($serviceType, $serviceId),
            ]
        );
        
        $snapshot->recalculate();
        $snapshot->save();
    }

    /**
     * Get admin stock by type and ID
     */
    private function getAdminStock(string $type, int $id): int
    {
        return match($type) {
            'kostum' => Costume::find($id)?->stock ?? 0,
            'rias' => MakeupService::find($id)?->total_slots ?? 0,
            'tari' => 0,
            default => 0
        };
    }
}
