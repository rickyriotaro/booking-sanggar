<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MakeupService;
use App\Models\StockSnapshot;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\JoinClause;

class MakeupServiceController extends Controller
{
    /**
     * Get average rating and reviews count for a service
     */
    private function getServiceRatingAndReviews($serviceType, $serviceId)
    {
        // Direct query to reviews table filtering by service_type and detail_id via orderDetail
        // Query path: Review -> OrderDetail -> filter by service_type='rias' and detail_id=$serviceId
        // Then filter Order by status in ['paid', 'completed']
        
        $baseQuery = Review::query()
            ->join('order_details', 'reviews.order_detail_id', '=', 'order_details.id')
            ->join('orders', 'reviews.order_id', '=', 'orders.id')
            ->where('order_details.service_type', $serviceType)
            ->where('order_details.detail_id', $serviceId)
            ->whereIn('orders.status', ['paid', 'completed']);
        
        $avgRating = (clone $baseQuery)->avg('reviews.rating');
        $reviewsCount = (clone $baseQuery)->count('reviews.id');

        return [
            'average_rating' => $avgRating ? round($avgRating, 1) : 0,
            'reviews_count' => (int)$reviewsCount
        ];
    }

    public function index(Request $request)
    {
        $query = MakeupService::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('package_name', 'like', '%' . $request->search . '%');
        }

        // Join dengan snapshot untuk get real-time stock
        $makeupServices = $query
            ->leftJoin('stock_snapshots', function (JoinClause $join) {
                $join->on('makeup_services.id', '=', 'stock_snapshots.service_id')
                     ->where('stock_snapshots.service_type', '=', 'rias');
            })
            ->select(
                'makeup_services.*',
                'stock_snapshots.stok_by_admin',
                'stock_snapshots.stok_from_orders',
                'stock_snapshots.sisa_stok_tersedia'
            )
            ->latest('makeup_services.created_at')
            ->paginate(5);
        return view('admin.makeup-services.index', compact('makeupServices'));
    }

    public function create()
    {
        return view('admin.makeup-services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'category' => 'required|in:SD,SMP,SMA,Wisuda,Acara Umum',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'total_slots' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['is_available'] = $request->has('is_available');
        $validated['total_slots'] = $request->input('total_slots', 10); // Default 10 slots

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/makeup-services');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/makeup-services
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'makeup-services/' . $filename;
        }

        MakeupService::create($validated);

        return redirect()->route('admin.makeup-services.index')
            ->with('success', 'Layanan makeup berhasil ditambahkan');
    }

    public function edit(MakeupService $makeupService)
    {
        $snapshot = StockSnapshot::where('service_type', 'rias')
            ->where('service_id', $makeupService->id)
            ->first();
        
        return view('admin.makeup-services.edit', compact('makeupService', 'snapshot'));
    }

    public function update(Request $request, MakeupService $makeupService)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'category' => 'required|in:SD,SMP,SMA,Wisuda,Acara Umum',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'total_slots' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['is_available'] = $request->has('is_available');
        $validated['total_slots'] = $request->input('total_slots', $makeupService->total_slots ?? 10);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($makeupService->image_path) {
                $filePath = public_path('storage/' . $makeupService->image_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/makeup-services');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/makeup-services
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'makeup-services/' . $filename;
        }

        $makeupService->update($validated);

        return redirect()->route('admin.makeup-services.index')
            ->with('success', 'Layanan makeup berhasil diperbarui');
    }

    public function destroy(MakeupService $makeupService)
    {
        // Delete image if exists
        if ($makeupService->image_path) {
            $filePath = public_path('storage/' . $makeupService->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $makeupService->delete();

        return redirect()->route('admin.makeup-services.index')
            ->with('success', 'Layanan makeup berhasil dihapus');
    }

    public function show(MakeupService $makeupService)
    {
        $makeupService->load('orderDetails.order');
        
        // Get rating and reviews data
        $ratingData = $this->getServiceRatingAndReviews('rias', $makeupService->id);
        
        return view('admin.makeup-services.show', compact('makeupService', 'ratingData'));
    }
}
