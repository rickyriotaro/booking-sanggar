@extends('layouts.admin')

@section('title', 'Detail Jasa Rias')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.makeup-services.index') }}" class="hover:text-red-800">Jasa Rias</a>
        <span>/</span>
        <span>Detail</span>
    </div>
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Detail Jasa Rias</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.makeup-services.edit', $makeupService) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <!-- Image Section -->
        @if($makeupService->image_path)
            <img src="{{ asset('storage/' . $makeupService->image_path) }}" alt="{{ $makeupService->package_name }}" class="w-full h-64 object-cover">
        @else
            <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Paket</h2>
        
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Nama Paket</div>
                    <div class="col-span-2 font-medium text-gray-900">{{ $makeupService->package_name }}</div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Kategori</div>
                    <div class="col-span-2">
                        @if($makeupService->category == 'SD')
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-medium">SD (Sekolah Dasar)</span>
                        @elseif($makeupService->category == 'SMP')
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded font-medium">SMP (Sekolah Menengah Pertama)</span>
                        @elseif($makeupService->category == 'SMA')
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-medium">SMA (Sekolah Menengah Atas)</span>
                        @elseif($makeupService->category == 'Wisuda')
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded font-medium">Wisuda</span>
                        @else
                        <span class="bg-pink-100 text-pink-800 px-3 py-1 rounded font-medium">{{ $makeupService->category }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Harga</div>
                    <div class="col-span-2 text-lg font-bold text-red-800">
                        Rp {{ number_format((float)$makeupService->price, 0, ',', '.') }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Semua Slot Tersedia</div>
                    <div class="col-span-2">
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded
                            @if($makeupService->total_slots <= 3) bg-red-100 text-red-800
                            @elseif($makeupService->total_slots <= 10) bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ $makeupService->total_slots }} slot
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Total Slot Terpakai</div>
                    <div class="col-span-2">
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded
                            @if($makeupService->orderDetails->filter(function($detail) {
                                return in_array($detail->order->status, ['paid', 'completed']) && 
                                       in_array($detail->item_return_status ?? 'belum', ['belum', null]);
                            })->count() >= $makeupService->total_slots) bg-red-100 text-red-800
                            @elseif($makeupService->orderDetails->filter(function($detail) {
                                return in_array($detail->order->status, ['paid', 'completed']) && 
                                       in_array($detail->item_return_status ?? 'belum', ['belum', null]);
                            })->count() >= ($makeupService->total_slots ?? 1) * 0.75) bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ $makeupService->orderDetails->filter(function($detail) {
                                return in_array($detail->order->status, ['paid', 'completed']) && 
                                       in_array($detail->item_return_status ?? 'belum', ['belum', null]);
                            })->count() }}/{{ $makeupService->total_slots ?? 1 }} Slot
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-sm text-gray-600">Status Ketersediaan</div>
                    <div class="col-span-2">
                        @if($makeupService->is_available)
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-medium">Tersedia</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-medium">Tidak Tersedia</span>
                        @endif
                    </div>
                </div>

                @if($makeupService->description)
                <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                    <div class="text-sm text-gray-600">Deskripsi</div>
                    <div class="col-span-2 text-gray-900">{{ $makeupService->description }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Info -->
    <div class="space-y-6">
        <!-- Statistik Rating & Views -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating & Tampilan</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Rating Rata-rata</span>
                    <div class="flex items-center gap-2">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($ratingData['average_rating']))
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @else
                                    <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endif
                            @endfor
                        </div>
                        <span class="font-bold text-gray-900">{{ $ratingData['average_rating'] }}/5</span>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Jumlah Review</span>
                    <span class="font-bold text-blue-600">{{ $ratingData['reviews_count'] }} review</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Tampilan</span>
                    <span class="font-bold text-purple-600">{{ $makeupService->views_count ?? 0 }} views</span>
                </div>
            </div>
        </div>

        <!-- Statistik Pesanan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Pesanan</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Pesanan</span>
                    <span class="font-bold text-gray-900">{{ $makeupService->orderDetails->filter(function($detail) {
                        return in_array($detail->order->status, ['paid', 'completed']);
                    })->count() }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Pendapatan</span>
                    <span class="font-bold text-green-600">
                        Rp {{ number_format($makeupService->orderDetails->filter(function($detail) {
                            return in_array($detail->order->status, ['paid', 'completed']);
                        })->sum(function($detail) {
                            return $detail->unit_price * $detail->quantity;
                        }), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Informasi Tambahan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Dibuat</span>
                    <span class="text-gray-900">{{ $makeupService->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Terakhir Update</span>
                    <span class="text-gray-900">{{ $makeupService->updated_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Pesanan -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pesanan</h2>
    
    @if($makeupService->orderDetails->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Acara</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Pembayaran</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Pengembalian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($makeupService->orderDetails->take(10) as $detail)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-blue-600">
                        <a href="{{ route('admin.orders.show', $detail->order) }}" class="hover:underline">
                            #{{ $detail->order->order_code }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->order->user->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        @if($detail->order->start_date && $detail->order->end_date)
                            {{ \Carbon\Carbon::parse($detail->order->start_date)->format('d M Y') }}
                            @if($detail->order->start_date != $detail->order->end_date)
                                - {{ \Carbon\Carbon::parse($detail->order->end_date)->format('d M Y') }}
                            @endif
                        @elseif($detail->order->event_date)
                            {{ $detail->order->event_date->format('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $detail->quantity }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        Rp {{ number_format($detail->unit_price * $detail->quantity, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($detail->order->status == 'completed')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Selesai</span>
                        @elseif($detail->order->status == 'expired')
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Expire</span>
                        @elseif($detail->order->status == 'paid')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Lunas</span>
                        @else
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ ucfirst($detail->order->status) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($detail->order->transaction && $detail->order->transaction->pg_status == 'expire')
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Gagal</span>
                        @elseif($detail->item_return_status == 'belum' || !$detail->item_return_status)
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Belum</span>
                        @elseif($detail->item_return_status == 'terlambat')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Terlambat</span>
                        @else
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Sudah</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($makeupService->orderDetails->count() > 10)
    <p class="text-sm text-gray-500 mt-3">Menampilkan 10 pesanan terakhir dari {{ $makeupService->orderDetails->count() }} total pesanan</p>
    @endif
    @else
    <div class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2">Belum ada pesanan untuk jasa rias ini</p>
    </div>
    @endif
</div>
@endsection
