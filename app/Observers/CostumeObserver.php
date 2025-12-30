<?php

namespace App\Observers;

use App\Models\Costume;
use App\Models\StockSnapshot;
use Illuminate\Support\Facades\Auth;

/**
 * Observer untuk auto-update stock snapshot saat admin edit/add costume
 * 
 * Fitur:
 * - stok_by_admin: Update dari costume.stock (admin set)
 * - sisa_stok: Recalculate dari stok_by_admin - stok_from_orders
 * - admin_history: Catat perubahan (siapa, kapan, qty berapa)
 */
class CostumeObserver
{
    /**
     * When costume is created - update stock snapshot
     */
    public function created(Costume $costume): void
    {
        $this->syncSnapshot($costume, 'Created');
    }

    /**
     * When costume is updated - update stock snapshot if stock/availability changed
     */
    public function updated(Costume $costume): void
    {
        if ($costume->isDirty('stock') || $costume->isDirty('is_available')) {
            $changes = [];
            
            if ($costume->isDirty('stock')) {
                $oldStock = $costume->getOriginal('stock');
                $newStock = $costume->stock;
                $changes[] = "stock {$oldStock} -> {$newStock}";
            }
            
            if ($costume->isDirty('is_available')) {
                $oldStatus = $costume->getOriginal('is_available') ? 'Available' : 'Unavailable';
                $newStatus = $costume->is_available ? 'Available' : 'Unavailable';
                $changes[] = "availability {$oldStatus} -> {$newStatus}";
            }
            
            $reason = 'Admin updated ' . implode(', ', $changes);
            $this->syncSnapshot($costume, $reason);
        }
    }

    /**
     * When costume is deleted - remove from stock snapshot
     */
    public function deleted(Costume $costume): void
    {
        StockSnapshot::where('service_type', 'kostum')
            ->where('service_id', $costume->id)
            ->delete();
    }

    /**
     * Sync costume to stock snapshot
     */
    private function syncSnapshot(Costume $costume, string $reason): void
    {
        $snapshot = StockSnapshot::updateOrCreate(
            [
                'service_type' => 'kostum',
                'service_id' => $costume->id,
            ],
            [
                'service_name' => $costume->costume_name,
                'stok_by_admin' => $costume->stock,
            ]
        );

        $snapshot->recalculate();
        $adminId = Auth::id() ?? 0;
        $snapshot->addAdminHistory($costume->stock, $adminId, $reason);
        $snapshot->save();
    }
}
