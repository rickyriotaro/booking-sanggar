<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\StockSnapshot;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CatalogController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Get average rating and reviews count for a service
     */
    private function getServiceRatingAndReviews($serviceType, $serviceId)
    {
        $avgRating = Review::whereHas('orderDetail', function ($query) use ($serviceType, $serviceId) {
            $query->where('service_type', $serviceType)
                ->where('detail_id', $serviceId);
        })
            ->avg('rating');

        $reviewsCount = Review::whereHas('orderDetail', function ($query) use ($serviceType, $serviceId) {
            $query->where('service_type', $serviceType)
                ->where('detail_id', $serviceId);
        })
            ->count();

        return [
            'average_rating' => $avgRating ? round($avgRating, 1) : 0,
            'reviews_count' => $reviewsCount
        ];
    }

    /**
     * Get detailed reviews for a service
     */
    private function getServiceReviews($serviceType, $serviceId, $limit = 10)
    {
        $reviews = Review::whereHas('orderDetail', function ($query) use ($serviceType, $serviceId) {
            $query->where('service_type', $serviceType)
                ->where('detail_id', $serviceId);
        })
            ->with('user')
            ->select('id', 'rating', 'comment', 'user_id', 'created_at')
            ->latest()
            ->limit($limit)
            ->get();
        
        return $reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->toIso8601String(),
                'user_id' => $review->user->id ?? null,
                'user_name' => $review->user ? $review->user->name : 'Anonymous',
                'user_email' => $review->user ? $review->user->email : null,
            ];
        });
    }

    /**
     * Get total orders for a service
     */
    private function getTotalOrders($serviceType, $serviceId)
    {
        return OrderDetail::where('service_type', $serviceType)
            ->where('detail_id', $serviceId)
            ->count();
    }

    public function costumes(Request $request)
    {
        $query = Costume::query();

        // Filter only available costumes (is_available = true)
        $query->where('is_available', 1);

        // Search
        if ($request->has('search')) {
            $query->where('costume_name', 'like', '%' . $request->search . '%');
        }

        // Filter by size
        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        $costumes = $query->latest()->paginate(10);

        // Add rating, views, and total orders to each costume
        $costumes->getCollection()->transform(function ($costume) {
            $ratingData = $this->getServiceRatingAndReviews('kostum', $costume->id);
            $totalOrders = $this->getTotalOrders('kostum', $costume->id);

            // Get stok_by_admin from stock_snapshot
            $snapshot = StockSnapshot::where('service_type', 'kostum')
                ->where('service_id', $costume->id)
                ->first();
            $stokByAdmin = $snapshot ? $snapshot->stok_by_admin : $costume->stock;

            return array_merge(
                $costume->toArray(),
                $ratingData,
                ['total_orders' => $totalOrders, 'stok_by_admin' => $stokByAdmin]
            );
        });

        return response()->json([
            'success' => true,
            'data' => $costumes
        ]);
    }

    public function costumeDetail($id)
    {
        $costume = Costume::find($id);

        if (!$costume) {
            return response()->json([
                'success' => false,
                'message' => 'Kostum tidak ditemukan'
            ], 404);
        }

        // Increment views
        $costume->incrementViews();

        // Get rating, reviews count, and detailed reviews
        $ratingData = $this->getServiceRatingAndReviews('kostum', $id);
        $totalOrders = $this->getTotalOrders('kostum', $id);
        $reviews = $this->getServiceReviews('kostum', $id);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $costume->fresh()->toArray(),
                $ratingData,
                [
                    'views_count' => $costume->fresh()->views_count,
                    'total_orders' => $totalOrders,
                    'reviews' => $reviews
                ]
            )
        ]);
    }

    public function danceServices()
    {
        // Filter only available services
        $services = DanceService::where('is_available', 1)
            ->orderBy('number_of_dancers')
            ->get();

        // Add rating, views, and total orders to each service
        $services = $services->map(function ($service) {
            $ratingData = $this->getServiceRatingAndReviews('tari', $service->id);
            $totalOrders = $this->getTotalOrders('tari', $service->id);
            return array_merge(
                $service->toArray(),
                $ratingData,
                ['total_orders' => $totalOrders]
            );
        });

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    public function danceServiceDetail($id)
    {
        $service = DanceService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Paket tari tidak ditemukan'
            ], 404);
        }

        // Increment views
        $service->incrementViews();

        // Get rating, reviews count, and detailed reviews
        $ratingData = $this->getServiceRatingAndReviews('tari', $id);
        $totalOrders = $this->getTotalOrders('tari', $id);
        $reviews = $this->getServiceReviews('tari', $id);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $service->fresh()->toArray(),
                $ratingData,
                [
                    'views_count' => $service->fresh()->views_count,
                    'total_orders' => $totalOrders,
                    'reviews' => $reviews
                ]
            )
        ]);
    }

    public function makeupServices()
    {
        // Filter only available services
        $services = MakeupService::where('is_available', 1)->get();

        // Add rating, views, and total orders to each service
        $services = $services->map(function ($service) {
            $ratingData = $this->getServiceRatingAndReviews('rias', $service->id);
            $totalOrders = $this->getTotalOrders('rias', $service->id);

            // Get stok_by_admin from stock_snapshot
            $snapshot = StockSnapshot::where('service_type', 'rias')
                ->where('service_id', $service->id)
                ->first();
            $stokByAdmin = $snapshot ? $snapshot->stok_by_admin : $service->total_slots;

            return array_merge(
                $service->toArray(),
                $ratingData,
                ['total_orders' => $totalOrders, 'stok_by_admin' => $stokByAdmin]
            );
        });

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    public function makeupServiceDetail($id)
    {
        $service = MakeupService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Paket rias tidak ditemukan'
            ], 404);
        }

        // Increment views
        $service->incrementViews();

        // Get rating, reviews count, and detailed reviews
        $ratingData = $this->getServiceRatingAndReviews('rias', $id);
        $totalOrders = $this->getTotalOrders('rias', $id);
        $reviews = $this->getServiceReviews('rias', $id);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $service->fresh()->toArray(),
                $ratingData,
                [
                    'views_count' => $service->fresh()->views_count,
                    'total_orders' => $totalOrders,
                    'reviews' => $reviews
                ]
            )
        ]);
    }

    public function checkStock(Request $request)
    {
        $validated = $request->validate([
            'costume_id' => 'required|exists:costumes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1'
        ]);

        $costume = Costume::find($validated['costume_id']);
        $availableStock = $costume->getAvailableStock(
            $validated['start_date'],
            $validated['end_date']
        );

        $isAvailable = $availableStock >= $validated['quantity'];

        return response()->json([
            'success' => true,
            'data' => [
                'available_stock' => $availableStock,
                'total_stock' => $costume->stock,
                'requested_quantity' => $validated['quantity'],
                'is_available' => $isAvailable
            ]
        ]);
    }

    /**
     * Check available slots for dance service
     * POST /api/check-dance-slots
     */
    public function checkDanceSlots(Request $request)
    {
        $validated = $request->validate([
            'dance_id' => 'required|exists:dance_services,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1'
        ]);

        $dance = DanceService::find($validated['dance_id']);
        $availableSlots = $dance->getAvailableSlots(
            $validated['start_date'],
            $validated['end_date']
        );

        $isAvailable = $availableSlots >= $validated['quantity'];

        return response()->json([
            'success' => true,
            'data' => [
                'available_slots' => $availableSlots,
                'total_slots' => $dance->stock,
                'requested_quantity' => $validated['quantity'],
                'is_available' => $isAvailable
            ]
        ]);
    }

    /**
     * Check available slots for makeup service
     * POST /api/check-makeup-slots
     */
    public function checkMakeupSlots(Request $request)
    {
        $validated = $request->validate([
            'makeup_id' => 'required|exists:makeup_services,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1'
        ]);

        $makeup = MakeupService::find($validated['makeup_id']);
        $availableSlots = $makeup->getAvailableSlots(
            $validated['start_date'],
            $validated['end_date']
        );

        $isAvailable = $availableSlots >= $validated['quantity'];

        return response()->json([
            'success' => true,
            'data' => [
                'available_slots' => $availableSlots,
                'total_slots' => $makeup->total_slots,
                'requested_quantity' => $validated['quantity'],
                'is_available' => $isAvailable
            ]
        ]);
    }

    /**
     * Get next availability for costume
     * GET /api/costumes/{id}/next-availability
     */
    public function getCostumeNextAvailability($id)
    {
        $costume = Costume::find($id);

        if (!$costume) {
            return response()->json([
                'success' => false,
                'message' => 'Kostum tidak ditemukan'
            ], 404);
        }

        $nextAvailability = $this->scheduleService->getNextAvailability('kostum', $id);

        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $id,
                'service_name' => $costume->costume_name,
                'service_type' => 'kostum',
                'next_availability' => $nextAvailability
            ]
        ]);
    }

    /**
     * Get next availability for makeup service
     * GET /api/makeup-services/{id}/next-availability
     */
    public function getMakeupNextAvailability($id)
    {
        $makeup = MakeupService::find($id);

        if (!$makeup) {
            return response()->json([
                'success' => false,
                'message' => 'Jasa rias tidak ditemukan'
            ], 404);
        }

        $nextAvailability = $this->scheduleService->getNextAvailability('rias', $id);

        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $id,
                'service_name' => $makeup->package_name,
                'service_type' => 'rias',
                'next_availability' => $nextAvailability
            ]
        ]);
    }
}
