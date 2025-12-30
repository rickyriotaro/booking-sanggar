<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Costume;
use App\Models\StockSnapshot;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\JoinClause;

class CostumeController extends Controller
{
    /**
     * Get average rating and reviews count for a service
     */
    private function getServiceRatingAndReviews($serviceType, $serviceId)
    {
        // Direct query to reviews table filtering by service_type and detail_id via orderDetail
        // Query path: Review -> OrderDetail -> filter by service_type='kostum' and detail_id=$serviceId
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
        $query = Costume::query();

        if ($request->filled('search')) {
            $query->where('costume_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by size
        if ($request->filled('size')) {
            $query->where('size', 'like', '%' . $request->size . '%');
        }

        // Join dengan snapshot untuk get real-time stock
        $costumes = $query
            ->leftJoin('stock_snapshots', function (JoinClause $join) {
                $join->on('costumes.id', '=', 'stock_snapshots.service_id')
                     ->where('stock_snapshots.service_type', '=', 'kostum');
            })
            ->select(
                'costumes.*',
                'stock_snapshots.stok_by_admin',
                'stock_snapshots.stok_from_orders',
                'stock_snapshots.sisa_stok_tersedia'
            )
            ->latest('costumes.created_at')
            ->paginate(5);

        return view('admin.costumes.index', compact('costumes'));
    }

    public function create()
    {
        return view('admin.costumes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'costume_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rental_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Convert checkbox to boolean
        $validated['is_available'] = $request->has('is_available');

        // Convert sizes array to comma-separated string
        if (isset($validated['sizes'])) {
            $validated['size'] = implode(', ', $validated['sizes']);
            unset($validated['sizes']);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/costumes');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/costumes
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'costumes/' . $filename;
        }

        Costume::create($validated);

        return redirect()->route('admin.costumes.index')
            ->with('success', 'Kostum berhasil ditambahkan');
    }

    public function edit(Costume $costume)
    {
        $snapshot = StockSnapshot::where('service_type', 'kostum')
            ->where('service_id', $costume->id)
            ->first();
        
        return view('admin.costumes.edit', compact('costume', 'snapshot'));
    }

    public function update(Request $request, Costume $costume)
    {
        $validated = $request->validate([
            'costume_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rental_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sizes' => 'nullable|array',
            'sizes.*' => 'string|max:50',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Convert checkbox to boolean
        $validated['is_available'] = $request->has('is_available');

        // Convert sizes array to comma-separated string
        if (isset($validated['sizes'])) {
            $validated['size'] = implode(', ', $validated['sizes']);
            unset($validated['sizes']);
        }

        if ($request->hasFile('image')) {
            // Delete old image from public/storage
            if ($costume->image_path) {
                $filePath = public_path('storage/' . $costume->image_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/costumes');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/costumes
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'costumes/' . $filename;
        }

        $costume->update($validated);

        return redirect()->route('admin.costumes.index')
            ->with('success', 'Kostum berhasil diperbarui');
    }

    public function destroy(Costume $costume)
    {
        if ($costume->image_path) {
            $filePath = public_path('storage/' . $costume->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $costume->delete();

        return redirect()->route('admin.costumes.index')
            ->with('success', 'Kostum berhasil dihapus');
    }

    public function show(Costume $costume)
    {
        $costume->load(['stockLogs' => function($query) {
            $query->latest()->take(20);
        }]);
        
        // Get rating and reviews data
        $ratingData = $this->getServiceRatingAndReviews('kostum', $costume->id);
        
        return view('admin.costumes.show', compact('costume', 'ratingData'));
    }
}
