<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockSnapshotService;
use Illuminate\Http\Request;

class StockSnapshotController extends Controller
{
    protected $stockSnapshotService;

    public function __construct(StockSnapshotService $stockSnapshotService)
    {
        $this->stockSnapshotService = $stockSnapshotService;
    }

    /**
     * Get stock snapshot for a service
     * GET /api/stock-snapshot/{serviceType}/{serviceId}
     */
    public function getSnapshot(string $serviceType, int $serviceId)
    {
        $data = $this->stockSnapshotService->getSnapshot($serviceType, $serviceId);

        if (isset($data['error'])) {
            return response()->json([
                'success' => false,
                'message' => $data['error']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Admin update stock
     * POST /api/stock-snapshot/{serviceType}/{serviceId}/update-stock
     * 
     * Request:
     * {
     *   "stok_by_admin": 15,
     *   "reason": "Admin menambah stok"
     * }
     */
    public function updateStock(string $serviceType, int $serviceId, Request $request)
    {
        $validated = $request->validate([
            'stok_by_admin' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500'
        ]);

        $data = $this->stockSnapshotService->getSnapshot($serviceType, $serviceId);
        
        if (isset($data['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        // Get service name from current data
        $serviceName = $data['service_name'];

        // Update snapshot
        $snapshot = $this->stockSnapshotService->createOrUpdateSnapshot(
            $serviceType,
            $serviceId,
            $serviceName,
            $validated['stok_by_admin'],
            auth()->user()?->id,
            $validated['reason'] ?? 'Admin update'
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => $this->stockSnapshotService->getSnapshot($serviceType, $serviceId)
        ]);
    }

    /**
     * Get stock history for a service
     * GET /api/stock-snapshot/{serviceType}/{serviceId}/history
     */
    public function getHistory(string $serviceType, int $serviceId, Request $request)
    {
        $limit = $request->query('limit', 50);
        $history = $this->stockSnapshotService->getHistory($serviceType, $serviceId, $limit);

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'history' => $history
            ]
        ]);
    }

    /**
     * Recalculate all snapshots (admin only)
     * POST /api/stock-snapshot/recalculate-all
     */
    public function recalculateAll()
    {
        $this->stockSnapshotService->recalculateAll();

        return response()->json([
            'success' => true,
            'message' => 'All stock snapshots recalculated'
        ]);
    }

    /**
     * Initialize all snapshots from current stock values (admin only)
     * POST /api/stock-snapshot/initialize-all
     */
    public function initializeAll()
    {
        $this->stockSnapshotService->initializeAll();

        return response()->json([
            'success' => true,
            'message' => 'All stock snapshots initialized'
        ]);
    }
}
