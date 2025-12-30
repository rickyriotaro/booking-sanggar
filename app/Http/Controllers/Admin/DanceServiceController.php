<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanceService;
use App\Models\StockSnapshot;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\JoinClause;

class DanceServiceController extends Controller
{
    /**
     * Get average rating and reviews count for a service
     */
    private function getServiceRatingAndReviews($serviceType, $serviceId)
    {
        // Direct query to reviews table filtering by service_type and detail_id via orderDetail
        // Query path: Review -> OrderDetail -> filter by service_type='tari' and detail_id=$serviceId
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
        $query = DanceService::query();

        if ($request->filled('search')) {
            $query->where('package_name', 'like', '%' . $request->search . '%')
                  ->orWhere('dance_type', 'like', '%' . $request->search . '%');
        }

        // Filter by dance type
        if ($request->filled('dance_type')) {
            $query->where('dance_type', $request->dance_type);
        }

        // Join dengan snapshot untuk get real-time stock
        $danceServices = $query
            ->leftJoin('stock_snapshots', function (JoinClause $join) {
                $join->on('dance_services.id', '=', 'stock_snapshots.service_id')
                     ->where('stock_snapshots.service_type', '=', 'tari');
            })
            ->select(
                'dance_services.*',
                'stock_snapshots.stok_by_admin',
                'stock_snapshots.stok_from_orders',
                'stock_snapshots.sisa_stok_tersedia'
            )
            ->latest('dance_services.created_at')
            ->paginate(5);
        return view('admin.dance-services.index', compact('danceServices'));
    }

    public function create()
    {
        return view('admin.dance-services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'dance_type' => 'required|string|max:255',
            'number_of_dancers' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Validasi jumlah penari harus ganjil
        if ($validated['number_of_dancers'] % 2 == 0) {
            return back()->withErrors(['number_of_dancers' => 'Jumlah penari harus ganjil (3, 5, 7, dst)'])
                ->withInput();
        }

        $validated['is_available'] = $request->has('is_available');

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/dance-services');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/dance-services
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'dance-services/' . $filename;
        }

        DanceService::create($validated);

        return redirect()->route('admin.dance-services.index')
            ->with('success', 'Paket jasa tari berhasil ditambahkan');
    }

    public function edit(DanceService $danceService)
    {
        $snapshot = StockSnapshot::where('service_type', 'tari')
            ->where('service_id', $danceService->id)
            ->first();
        
        return view('admin.dance-services.edit', compact('danceService', 'snapshot'));
    }

    public function update(Request $request, DanceService $danceService)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'dance_type' => 'required|string|max:255',
            'number_of_dancers' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Validasi jumlah penari harus ganjil
        if ($validated['number_of_dancers'] % 2 == 0) {
            return back()->withErrors(['number_of_dancers' => 'Jumlah penari harus ganjil (3, 5, 7, dst)'])
                ->withInput();
        }

        $validated['is_available'] = $request->has('is_available');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($danceService->image_path) {
                $filePath = public_path('storage/' . $danceService->image_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $uploadDir = public_path('storage/dance-services');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file directly to public/storage/dance-services
            $file->move($uploadDir, $filename);
            $validated['image_path'] = 'dance-services/' . $filename;
        }

        $danceService->update($validated);

        return redirect()->route('admin.dance-services.index')
            ->with('success', 'Paket jasa tari berhasil diperbarui');
    }

    public function destroy(DanceService $danceService)
    {
        // Delete image if exists
        if ($danceService->image_path) {
            $filePath = public_path('storage/' . $danceService->image_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $danceService->delete();

        return redirect()->route('admin.dance-services.index')
            ->with('success', 'Paket jasa tari berhasil dihapus');
    }

    public function show(DanceService $danceService)
    {
        $danceService->load('orderDetails.order');
        
        // Get rating and reviews data
        $ratingData = $this->getServiceRatingAndReviews('tari', $danceService->id);
        
        return view('admin.dance-services.show', compact('danceService', 'ratingData'));
    }
}
