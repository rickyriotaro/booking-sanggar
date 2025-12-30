<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Costume;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        // 1. Total Users (excluding admin)
        $totalUsers = User::whereIn('role', ['user', 'customer'])->count();
        
        // 2. Active Orders - Orders yang sudah dibayar (paid) tapi belum dikembalikan
        $activeOrders = Order::where('status', 'paid')
            ->where(function($query) {
                $query->whereNull('return_status')
                      ->orWhere('return_status', 'belum');
            })
            ->count();
        
        // 3. Returned Orders - Orders yang sudah dikembalikan (return_status: sudah atau terlambat)
        $returnedOrders = Order::whereIn('return_status', ['sudah', 'terlambat'])->count();
        
        // Keep these for other purposes
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $totalRevenue = Transaction::where('pg_status', 'settlement')->sum('amount');
        
        
        // Low Stock Items - Combine Costumes and Makeup Services
        // Get low stock costumes
        $lowStockCostumesData = Costume::where('stock', '<=', 3)
            ->select('id', 'costume_name as name', 'size as detail', 'stock', DB::raw("'costume' as item_type"))
            ->get();
        
        // Get low slots makeup services
        $lowSlotsMakeupData = DB::table('makeup_services')
            ->where('total_slots', '<=', 3)
            ->select('id', 'package_name as name', 'category as detail', 'total_slots as stock', DB::raw("'makeup' as item_type"))
            ->get();
        
        // Combine both
        $lowStockItems = collect($lowStockCostumesData)
            ->merge($lowSlotsMakeupData)
            ->sortBy('stock');
        
        // Recent orders - untuk card "Pesanan Terbaru" (5 data)
        $recentOrders = Order::with(['user', 'transaction'])
            ->latest()
            ->take(5)
            ->get();
        
        // Monthly revenue (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $total = Transaction::where('pg_status', 'settlement')
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'total' => $total
            ];
        }
        
        // Return Status Distribution for Pie Chart
        // Count orders by return status
        $returnStatusSudah = Order::where('return_status', 'sudah')->count();
        $returnStatusTerlambat = Order::where('return_status', 'terlambat')->count();
        $returnStatusGagal = Order::where('return_status', 'gagal')->count();
        
        // Return status 'belum' - hanya yang sudah paid
        $returnStatusBelum = Order::where('status', 'paid')
            ->where(function($query) {
                $query->whereNull('return_status')
                      ->orWhere('return_status', 'belum');
            })
            ->count();
        
        $returnStatusData = [
            'sudah' => $returnStatusSudah,
            'terlambat' => $returnStatusTerlambat,
            'gagal' => $returnStatusGagal,
            'belum' => $returnStatusBelum
        ];
        
        // Top 5 Items - Combine Costumes, Dance Services, and Makeup Services
        // Get top costumes
        $topCostumes = DB::table('costumes')
            ->leftJoin('order_details', function($join) {
                $join->on('costumes.id', '=', 'order_details.detail_id')
                     ->where('order_details.service_type', '=', 'kostum');
            })
            ->leftJoin('orders', function($join) {
                $join->on('order_details.order_id', '=', 'orders.id')
                     ->whereIn('orders.status', ['confirmed', 'completed', 'paid']);
            })
            ->select(
                'costumes.id as item_id',
                'costumes.costume_name as item_name',
                'costumes.size as item_detail',
                DB::raw("'Kostum' as item_type"),
                DB::raw('COUNT(DISTINCT order_details.id) as rental_count')
            )
            ->groupBy('costumes.id', 'costumes.costume_name', 'costumes.size')
            ->get();
        
        // Get top dance services
        $topDance = DB::table('dance_services')
            ->leftJoin('order_details', function($join) {
                $join->on('dance_services.id', '=', 'order_details.detail_id')
                     ->where('order_details.service_type', '=', 'tari');
            })
            ->leftJoin('orders', function($join) {
                $join->on('order_details.order_id', '=', 'orders.id')
                     ->whereIn('orders.status', ['confirmed', 'completed', 'paid']);
            })
            ->select(
                'dance_services.id as item_id',
                'dance_services.package_name as item_name',
                'dance_services.dance_type as item_detail',
                DB::raw("'Jasa Tari' as item_type"),
                DB::raw('COUNT(DISTINCT order_details.id) as rental_count')
            )
            ->groupBy('dance_services.id', 'dance_services.package_name', 'dance_services.dance_type')
            ->get();
        
        // Get top makeup services
        $topMakeup = DB::table('makeup_services')
            ->leftJoin('order_details', function($join) {
                $join->on('makeup_services.id', '=', 'order_details.detail_id')
                     ->where('order_details.service_type', '=', 'rias');
            })
            ->leftJoin('orders', function($join) {
                $join->on('order_details.order_id', '=', 'orders.id')
                     ->whereIn('orders.status', ['confirmed', 'completed', 'paid']);
            })
            ->select(
                'makeup_services.id as item_id',
                'makeup_services.package_name as item_name',
                'makeup_services.category as item_detail',
                DB::raw("'Jasa Rias' as item_type"),
                DB::raw('COUNT(DISTINCT order_details.id) as rental_count')
            )
            ->groupBy('makeup_services.id', 'makeup_services.package_name', 'makeup_services.category')
            ->get();
        
        // Combine all items and sort by rental_count
        $topItems = collect($topCostumes)
            ->merge($topDance)
            ->merge($topMakeup)
            ->sortByDesc('rental_count')
            ->take(5)
            ->values();
        
        // Top 5 Users by Total Transaction Amount - Using join for better reliability
        $topUsersData = DB::table('users')
            ->leftJoin('orders', function($join) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->whereIn('orders.status', ['confirmed', 'completed', 'paid']);
            })
            ->whereIn('users.role', ['user', 'customer'])
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_spent'),
                DB::raw('COUNT(DISTINCT orders.id) as completed_orders')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
        
        // Convert to collection
        $topUsers = collect($topUsersData)->map(function($user) {
            $userObj = new \stdClass();
            $userObj->id = $user->id;
            $userObj->name = $user->name;
            $userObj->email = $user->email;
            $userObj->total_spent = $user->total_spent;
            $userObj->completed_orders = $user->completed_orders;
            return $userObj;
        });

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeOrders',
            'returnedOrders',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalRevenue',
            'lowStockItems',
            'recentOrders',
            'monthlyRevenue',
            'returnStatusData',
            'topItems',
            'topUsers'
        ));
    }
}
