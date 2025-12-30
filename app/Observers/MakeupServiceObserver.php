<?php

namespace App\Observers;

use App\Models\MakeupService;
use App\Models\StockSnapshot;
use Illuminate\Support\Facades\Auth;

/**
 * Observer untuk auto-update stock snapshot saat admin add/edit/delete makeup service
 */
class MakeupServiceObserver
{
    /**
     * When makeup service is created - update stock snapshot
     */
    public function created(MakeupService $makeup): void
    {
        $this->syncSnapshot($makeup, 'Created');
    }

    /**
     * When makeup service is updated - update stock snapshot if slots/availability changed
     */
    public function updated(MakeupService $makeup): void
    {
        if ($makeup->isDirty('total_slots') || $makeup->isDirty('is_available')) {
            $changes = [];
            
            if ($makeup->isDirty('total_slots')) {
                $oldSlots = $makeup->getOriginal('total_slots');
                $newSlots = $makeup->total_slots;
                $changes[] = "slots {$oldSlots} -> {$newSlots}";
            }
            
            if ($makeup->isDirty('is_available')) {
                $oldStatus = $makeup->getOriginal('is_available') ? 'Available' : 'Unavailable';
                $newStatus = $makeup->is_available ? 'Available' : 'Unavailable';
                $changes[] = "availability {$oldStatus} -> {$newStatus}";
            }
            
            $reason = 'Admin updated ' . implode(', ', $changes);
            $this->syncSnapshot($makeup, $reason);
        }
    }

    /**
     * When makeup service is deleted - remove from stock snapshot
     */
    public function deleted(MakeupService $makeup): void
    {
        StockSnapshot::where('service_type', 'rias')
            ->where('service_id', $makeup->id)
            ->delete();
    }

    /**
     * Sync makeup service to stock snapshot
     */
    private function syncSnapshot(MakeupService $makeup, string $reason): void
    {
        $snapshot = StockSnapshot::updateOrCreate(
            [
                'service_type' => 'rias',
                'service_id' => $makeup->id,
            ],
            [
                'service_name' => $makeup->package_name,
                'stok_by_admin' => $makeup->total_slots ?? 0,
            ]
        );

        $snapshot->recalculate();
        $adminId = Auth::id() ?? 0;
        $snapshot->addAdminHistory($makeup->total_slots ?? 0, $adminId, $reason);
        $snapshot->save();
    }
}
