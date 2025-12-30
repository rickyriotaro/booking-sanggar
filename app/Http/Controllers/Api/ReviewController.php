<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a review for a specific order item
     */
    public function store(Request $request, $orderId, $itemId)
    {
        Log::info('📝 Review submission attempt', [
            'orderId' => $orderId,
            'itemId' => $itemId,
            'userId' => $request->user()->id,
            'data' => $request->all()
        ]);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        // Verify order belongs to user
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            Log::error('❌ Order not found or user mismatch', [
                'orderId' => $orderId,
                'userId' => $request->user()->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        // Verify order detail exists and belongs to this order
        $orderDetail = OrderDetail::where('id', $itemId)
            ->where('order_id', $orderId)
            ->first();

        if (!$orderDetail) {
            Log::error('❌ OrderDetail not found', [
                'itemId' => $itemId,
                'orderId' => $orderId
            ]);
            Log::info('📊 Available order_details in this order:', [
                'details' => OrderDetail::where('order_id', $orderId)->get(['id', 'detail_id', 'service_type'])->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan dalam pesanan ini'
            ], 404);
        }

        // Check if already reviewed (one-time only)
        $existingReview = Review::where('order_detail_id', $itemId)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            Log::warning('⚠️ Review already exists', [
                'itemId' => $itemId,
                'userId' => $request->user()->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Item ini sudah direview dan tidak dapat diubah'
            ], 400);
        }

        // Create review
        $review = Review::create([
            'order_id' => $order->id,
            'order_detail_id' => $orderDetail->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null
        ]);

        Log::info('✅ Review created successfully', [
            'reviewId' => $review->id,
            'orderDetailId' => $review->order_detail_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil ditambahkan',
            'data' => $review
        ], 201);
    }

    /**
     * Get review for a specific order item
     */
    public function getItemReview(Request $request, $orderId, $itemId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        $review = Review::where('order_detail_id', $itemId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Get all reviews for an order
     */
    public function getOrderReviews(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        $reviews = Review::where('order_id', $orderId)
            ->with('orderDetail')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get all user reviews (paginated)
     */
    public function myReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['order', 'orderDetail'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
