@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .stat-card {
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .top-item-card {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    
    .top-item-card:hover {
        border-left-color: #dc2626;
        background-color: #fef2f2;
    }
    
    .rank-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    .rank-1 { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #975A16; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #e8e8e8 100%); color: #4a5568; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #e8a87c 100%); color: #fff; }
    .rank-other { background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%); color: #4a5568; }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card 1: Total Users -->
    <div class="stat-card rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: rgba(255, 255, 255, 0.9);">Total User</p>
                <h3 class="text-4xl font-bold mt-2">{{ $totalUsers }}</h3>
            </div>
            <div class="p-4 rounded-lg" style="background: rgba(255, 255, 255, 0.2);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 2: Active Orders (Sedang Berjalan) -->
    <div class="stat-card rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: rgba(255, 255, 255, 0.9);">Order Berjalan</p>
                <h3 class="text-4xl font-bold mt-2">{{ $activeOrders }}</h3>
            </div>
            <div class="p-4 rounded-lg" style="background: rgba(255, 255, 255, 0.2);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 3: Returned Orders (Sudah Dikembalikan) -->
    <div class="stat-card rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: rgba(255, 255, 255, 0.9);">Item Dikembalikan</p>
                <h3 class="text-4xl font-bold mt-2">{{ $returnedOrders }}</h3>
            </div>
            <div class="p-4 rounded-lg" style="background: rgba(255, 255, 255, 0.2);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Revenue -->
    <div class="stat-card rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: rgba(255, 255, 255, 0.9);">Total Pendapatan</p>
                <h3 class="text-3xl font-bold mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            </div>
            <div class="p-4 rounded-lg" style="background: rgba(255, 255, 255, 0.2);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Monthly Revenue Bar Chart -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Pendapatan 6 Bulan Terakhir</h3>
            <span class="text-sm text-gray-500">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </span>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Return Status Pie Chart -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Status Pengembalian</h3>
            <span class="text-sm text-gray-500">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
            </span>
        </div>
        <div class="chart-container">
            <canvas id="returnStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Items and Top Users -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top 5 Items (Kostum, Tari, Rias) -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">🏆 Top 5 Item Terpopuler</h3>
            <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-xs font-semibold">Hot Items</span>
        </div>
        
        @if($topItems->count() > 0)
            <div class="space-y-3">
                @foreach($topItems as $index => $item)
                <div class="top-item-card p-4 rounded-lg bg-gray-50 flex items-center gap-4">
                    <div class="rank-badge rank-{{ $index < 3 ? $index + 1 : 'other' }}">
                        #{{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800">{{ $item->item_name }}</p>
                            <span class="px-2 py-0.5 text-xs rounded-full 
                                @if($item->item_type == 'Kostum') bg-blue-100 text-blue-700
                                @elseif($item->item_type == 'Jasa Tari') bg-purple-100 text-purple-700
                                @else bg-pink-100 text-pink-700
                                @endif">
                                {{ $item->item_type }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $item->item_detail }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600">{{ $item->rental_count }}</p>
                        <p class="text-xs text-gray-500">kali disewa</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-500">Belum ada data pesanan</p>
            </div>
        @endif
    </div>

    <!-- Top 5 Customers -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">👥 Top 5 Pelanggan</h3>
            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold">VIP</span>
        </div>
        
        @if($topUsers->count() > 0)
            <div class="space-y-3">
                @foreach($topUsers as $index => $user)
                <div class="top-item-card p-4 rounded-lg bg-gray-50 flex items-center gap-4">
                    <div class="rank-badge rank-{{ $index < 3 ? $index + 1 : 'other' }}">
                        #{{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->completed_orders }} pesanan</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-600">Rp {{ number_format($user->total_spent ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">total belanja</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-gray-500">Belum ada data pelanggan</p>
            </div>
        @endif
    </div>
</div>

<!-- Today's Schedule and Low Stock -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Pesanan Terbaru (5 Data) -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Pesanan Terbaru</h3>
        </div>
        @if($recentOrders->count() > 0)
            <div class="space-y-3">
                @foreach($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="block p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border border-purple-100 hover:border-purple-300 hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-purple-600 text-black text-xs font-bold rounded">
                                {{ $order->order_code }}
                            </span>
                            
                        </div>
                        <p class="text-lg font-bold text-purple-700">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            {{ $order->created_at->format('d M Y, H:i') }}
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if($order->transaction && $order->transaction->pg_status == 'settlement') 
                                bg-green-100 text-green-800
                            @elseif($order->transaction && $order->transaction->pg_status == 'pending') 
                                bg-yellow-100 text-yellow-800
                            @elseif($order->status == 'paid')
                                bg-blue-100 text-blue-800
                            @else 
                                bg-gray-100 text-gray-800
                            @endif">
                            @if($order->transaction)
                                {{ ucfirst($order->transaction->pg_status) }}
                            @else
                                {{ ucfirst($order->status) }}
                            @endif
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500">Belum ada pesanan</p>
            </div>
        @endif
    </div>

    <!-- Stok/Slot Rendah -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Stok/Slot Rendah (≤ 3)</h3>
        </div>
        @if($lowStockItems->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($lowStockItems as $item)
                <a href="@if($item->item_type == 'costume'){{ route('admin.costumes.show', $item->id) }}@else{{ route('admin.makeup-services.show', $item->id) }}@endif" 
                   class="flex items-center justify-between p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-lg border border-red-100 hover:border-red-300 hover:shadow-md transition-all">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-medium text-gray-800">{{ $item->name }}</p>
                            <span class="px-2 py-0.5 text-xs rounded-full 
                                @if($item->item_type == 'costume') 
                                    bg-blue-100 text-blue-700
                                @else 
                                    bg-pink-100 text-pink-700
                                @endif">
                                {{ $item->item_type == 'costume' ? 'Kostum' : 'Jasa Rias' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $item->detail }}</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 flex-shrink-0">
                        @if($item->item_type == 'costume')
                            Stok: {{ $item->stock }}
                        @else
                            Slot: {{ $item->stock }}
                        @endif
                    </span>
                </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-green-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500">Semua stok & slot aman</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Monthly Revenue Bar Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: {!! json_encode(array_column($monthlyRevenue, 'total')) !!},
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgba(220, 38, 38, 1)',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(220, 38, 38, 1)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'k';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Return Status Pie Chart
    const returnStatusCtx = document.getElementById('returnStatusChart').getContext('2d');
    const returnStatusChart = new Chart(returnStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Belum Dikembalikan', 'Sudah Dikembalikan', 'Terlambat', 'Gagal'],
            datasets: [{
                data: [
                    {{ $returnStatusData['belum'] }},
                    {{ $returnStatusData['sudah'] }},
                    {{ $returnStatusData['terlambat'] }},
                    {{ $returnStatusData['gagal'] }}
                ],
                backgroundColor: [
                    'rgba(251, 191, 36, 0.8)',  // Yellow - Belum
                    'rgba(34, 197, 94, 0.8)',   // Green - Sudah
                    'rgba(249, 115, 22, 0.8)',  // Orange - Terlambat
                    'rgba(239, 68, 68, 0.8)'    // Red - Gagal
                ],
                borderColor: [
                    'rgba(245, 158, 11, 1)',
                    'rgba(22, 163, 74, 1)',
                    'rgba(234, 88, 12, 1)',
                    'rgba(220, 38, 38, 1)'
                ],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
