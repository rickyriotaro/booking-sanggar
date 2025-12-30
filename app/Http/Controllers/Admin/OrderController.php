<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StockLog;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\StockSnapshot;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderDetails', 'transaction']);

        // Search by order code, customer name, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(5);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'orderDetails.costume',
            'orderDetails.danceService',
            'orderDetails.makeupService',
            'transaction',
            'review'
        ]);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        // NEW: Support per-item return status (Phase 6)
        $itemReturnStatuses = $request->input('item_return_status', []);
        $itemReturnDates = $request->input('item_return_date', []);
        
        // Validate per-item data if provided
        if (!empty($itemReturnStatuses)) {
            $validated = $request->validate([
                'item_return_status.*' => 'required|in:belum,sudah,terlambat',
                'item_return_date.*' => 'nullable|date_format:Y-m-d'
            ]);
        } else {
            // Fallback: old order-level validation for backward compatibility
            $validated = $request->validate([
                'return_status' => 'required|in:belum,sudah,terlambat',
                'actual_return_date' => 'nullable|required_if:return_status,terlambat|date_format:Y-m-d\TH:i'
            ]);
        }

        DB::beginTransaction();
        try {
            // NEW LOGIC: Handle per-item return status updates (Phase 6)
            if (!empty($itemReturnStatuses)) {
                foreach ($order->orderDetails as $index => $detail) {
                    if (isset($itemReturnStatuses[$index])) {
                        $oldStatus = $detail->item_return_status ?? 'belum';
                        $newStatus = $itemReturnStatuses[$index];
                        $returnDate = $itemReturnDates[$index] ?? null;
                        
                        // Validate: can't mark as returned before deadline (unless admin force returns late items)
                        $itemEndDate = $detail->item_end_date ?? $order->end_date;
                        $itemEndDateCarbon = \Carbon\Carbon::parse($itemEndDate)->endOfDay();
                        
                        // NOTE: Don't restore stock here - let OrderObserver handle snapshot recalculation
                        // The snapshot will automatically recalculate based on return_status
                        Log::info('📥 Marking item ' . $detail->id . ' as returned from status "' . $oldStatus . '" to "' . $newStatus . '"');
                        
                        // Update item-level return status
                        if (Schema::hasColumn('order_details', 'item_return_status')) {
                            $detail->update([
                                'item_return_status' => $newStatus,
                                'item_return_date' => $returnDate ?: ($newStatus !== 'belum' ? now()->format('Y-m-d') : null)
                            ]);
                        }
                        
                        // Recalculate snapshot for this service
                        $snapshot = StockSnapshot::where('service_type', $detail->service_type)
                            ->where('service_id', $detail->detail_id)
                            ->first();
                        
                        if ($snapshot) {
                            $snapshot->recalculate();
                            $snapshot->save();
                        }
                    }
                }
                
                // Update order-level status based on item statuses
                $allStatuses = $order->orderDetails->pluck('item_return_status')->unique();
                if ($allStatuses->count() == 1) {
                    // All items have same status
                    $order->update([
                        'return_status' => $allStatuses->first() ?? 'belum'
                    ]);
                } else {
                    // Mixed statuses - set order to 'belum' until all items are returned
                    $hasReturned = $allStatuses->contains('sudah') || $allStatuses->contains('terlambat');
                    $hasNotReturned = $allStatuses->contains('belum');
                    
                    if ($hasNotReturned) {
                        $order->update(['return_status' => 'belum']);
                    } else {
                        // All items returned (either sudah or terlambat)
                        $order->update(['return_status' => $allStatuses->contains('terlambat') ? 'terlambat' : 'sudah']);
                    }
                }
                
            } else {
                // FALLBACK: Old order-level logic (for backward compatibility)
                $oldReturnStatus = $order->return_status;
                $updateData = [
                    'return_status' => $validated['return_status']
                ];

                // If terlambat, store actual return date
                if ($validated['return_status'] === 'terlambat' && isset($validated['actual_return_date'])) {
                    $actualReturnDateTime = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['actual_return_date']);
                    $updateData['actual_return_date'] = $actualReturnDateTime;
                    $updateData['end_date'] = $actualReturnDateTime;
                }

                $order->update($updateData);

                // Update all items with same status
                if (($validated['return_status'] === 'sudah' || $validated['return_status'] === 'terlambat') && $oldReturnStatus !== 'sudah' && $oldReturnStatus !== 'terlambat') {
                    $returnDate = $validated['return_status'] === 'terlambat' 
                        ? ($validated['actual_return_date'] ?? now()->format('Y-m-d'))
                        : now()->format('Y-m-d');

                    foreach ($order->orderDetails as $detail) {
                        if (Schema::hasColumn('order_details', 'item_return_status')) {
                            $detail->update([
                                'item_return_status' => $validated['return_status'],
                                'item_return_date' => $returnDate
                            ]);
                        }

                        $snapshot = StockSnapshot::where('service_type', $detail->service_type)
                            ->where('service_id', $detail->detail_id)
                            ->first();
                        
                        if ($snapshot) {
                            $snapshot->recalculate();
                            $snapshot->save();
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status pengembalian berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ];

        $filename = 'pemesanan_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new OrdersExport($filters), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = Order::with(['user', 'orderDetails']);

        // Apply filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();
        $filters = $request->only(['start_date', 'end_date', 'status']);

        $pdf = Pdf::loadView('admin.orders.pdf', compact('orders', 'filters'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('pemesanan_' . date('Y-m-d_His') . '.pdf');
    }
}
