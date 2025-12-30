<?php

namespace App\Services;

use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\StockSnapshot;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;

class StockSnapshotService
{
    /**
     * Initialize snapshots for all services (call once during setup)
     */
    public function initializeAll()
    {
        // Kostum
        foreach (Costume::all() as $costume) {
            $this->createOrUpdateSnapshot('kostum', $costume->id, $costume->costume_name, $costume->stock);
        }

        // Makeup
        foreach (MakeupService::all() as $makeup) {
            $this->createOrUpdateSnapshot('rias', $makeup->id, $makeup->package_name, $makeup->total_slots);
        }

        // Dance
        foreach (DanceService::all() as $dance) {
            $this->createOrUpdateSnapshot('tari', $dance->id, $dance->package_name, $dance->available_slots ?? 0);
        }
    }

    /**
     * Create or update snapshot for a service
     */
    public function createOrUpdateSnapshot(
        string $serviceType,
        int $serviceId,
        string $serviceName,
        int $stokByAdmin,
        ?int $adminId = null,
        ?string $reason = null
    ): StockSnapshot {
        // Find or create snapshot
        $snapshot = StockSnapshot::firstOrCreate(
            [
                'service_type' => $serviceType,
                'service_id' => $serviceId
            ],
            [
                'service_name' => $serviceName,
                'stok_by_admin' => $stokByAdmin,
                'admin_history' => json_encode([
                    [
                        'qty' => $stokByAdmin,
                        'admin_id' => $adminId,
                        'reason' => $reason,
                        'date' => now()->toIso8601String(),
                    ]
                ]),
                'last_edited_by_admin' => $adminId,
                'last_edited_at' => now(),
                'edit_reason' => $reason
            ]
        );

        // Jika sudah ada dan ada perubahan, update
        if ($snapshot->stok_by_admin != $stokByAdmin) {
            // Add to history
            $snapshot->addAdminHistory($stokByAdmin, $adminId, $reason);
            
            // Update fields (admin_history is array, will auto-encode via cast)
            $snapshot->update([
                'stok_by_admin' => $stokByAdmin,
                'admin_history' => $snapshot->admin_history,  // Akan di-cast ke JSON otomatis
                'last_edited_by_admin' => $adminId,
                'last_edited_at' => now(),
                'edit_reason' => $reason
            ]);

            // Log ke StockHistory
            StockHistory::log(
                $serviceType,
                $serviceId,
                $serviceName,
                $snapshot->stok_by_admin,
                $stokByAdmin,
                'admin_edit',
                null,
                $adminId,
                $reason
            );
        }

        // Recalculate sisa stok
        $snapshot->recalculate();

        return $snapshot;
    }

    /**
     * Get stock snapshot for Flutter
     */
    public function getSnapshot(string $serviceType, int $serviceId): array
    {
        $snapshot = StockSnapshot::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->first();

        if (!$snapshot) {
            return [
                'error' => 'Stock snapshot not found',
                'service_type' => $serviceType,
                'service_id' => $serviceId
            ];
        }

        return [
            'service_type' => $snapshot->service_type,
            'service_id' => $snapshot->service_id,
            'service_name' => $snapshot->service_name,
            'stok_by_admin' => $snapshot->stok_by_admin,
            'stok_from_orders' => $snapshot->stok_from_orders,
            'sisa_stok_setelah_booking' => $snapshot->sisa_stok_setelah_booking,
            'last_booking_date' => $snapshot->last_booking_date,
            'admin_history' => $snapshot->admin_history,
            'warning' => $snapshot->stok_by_admin < $snapshot->stok_from_orders ? 
                'Stok asli lebih kecil dari yang di-order. Hubungi admin!' : null,
            'last_updated' => $snapshot->updated_at
        ];
    }

    /**
     * Recalculate all snapshots (call when needed)
     */
    public function recalculateAll()
    {
        $snapshots = StockSnapshot::all();
        foreach ($snapshots as $snapshot) {
            $snapshot->recalculate();
        }
    }

    /**
     * Get stock history for a service
     */
    public function getHistory(string $serviceType, int $serviceId, int $limit = 50): array
    {
        return StockHistory::where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
