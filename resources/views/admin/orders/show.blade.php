@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.orders.index') }}" class="hover:text-red-800">Pesanan</a>
        <span>/</span>
        <span>Detail</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail Pesanan #{{ $order->order_code }}</h1>
</div>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Detail Pesanan -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Jasa yang Dipesan</h2>
                @if($order->transaction)
                    <div class="text-right">
                        @if($order->transaction->pg_status == 'settlement')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">✓ Settlement</span>
                        @elseif($order->transaction->pg_status == 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">⏳ Pending</span>
                        @elseif($order->transaction->pg_status == 'expire')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">✗ Expired</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">✗ {{ ucfirst($order->transaction->pg_status) }}</span>
                        @endif
                    </div>
                @endif
            </div>
            
            @forelse($order->orderDetails as $detail)
                <div class="mb-6 pb-6 border-b last:border-b-0 last:mb-0 last:pb-0">
                    <!-- Service Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            @if($detail->service_type == 'kostum')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-purple-100 text-purple-800">👗 Kostum</span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $detail->costume->costume_name }}</h3>
                            @elseif($detail->service_type == 'tari')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-pink-100 text-pink-800">💃 Jasa Tari</span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $detail->danceService->package_name }}</h3>
                            @elseif($detail->service_type == 'rias')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">💄 Jasa Rias</span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $detail->makeupService->package_name }}</h3>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">Harga Satuan</p>
                            <p class="text-2xl font-bold text-red-600">Rp {{ number_format((float)$detail->unit_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                        @if($detail->service_type == 'kostum')
                            <div>
                                <p class="text-sm text-gray-600">Ukuran</p>
                                <p class="font-semibold text-gray-900">{{ $detail->costume->size ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jumlah</p>
                                <p class="font-semibold text-gray-900">{{ $detail->quantity }} buah</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Periode Pemakaian</p>
                                <p class="font-semibold text-gray-900">
                                    @if($detail->item_start_date && $detail->item_end_date)
                                        @php
                                            $itemStartDate = is_string($detail->item_start_date) ? \Carbon\Carbon::parse($detail->item_start_date) : $detail->item_start_date;
                                            $itemEndDate = is_string($detail->item_end_date) ? \Carbon\Carbon::parse($detail->item_end_date) : $detail->item_end_date;
                                        @endphp
                                        {{ $itemStartDate->format('d M Y') }} - {{ $itemEndDate->format('d M Y') }}
                                        @if($detail->rental_time)
                                            <span class="text-gray-600 font-normal">(Jam: {{ $detail->rental_time }})</span>
                                        @endif
                                    @else
                                        {{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($detail->costume->description)
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Deskripsi</p>
                                <p class="text-gray-700">{{ $detail->costume->description }}</p>
                            </div>
                            @endif
                        @elseif($detail->service_type == 'tari')
                            <div>
                                <p class="text-sm text-gray-600">Jenis Tarian</p>
                                <p class="font-semibold text-gray-900">{{ $detail->danceService->dance_type ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Penari</p>
                                <p class="font-semibold text-gray-900">{{ $detail->danceService->number_of_dancers }} orang</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Durasi</p>
                                <p class="font-semibold text-gray-900">{{ $detail->danceService->duration_minutes }} menit</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kuantitas Booking</p>
                                <p class="font-semibold text-gray-900">{{ $detail->quantity }}x</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">📅 Jadwal</p>
                                <p class="font-semibold text-gray-900">
                                    @if($detail->item_start_date && $detail->item_end_date)
                                        @php
                                            $itemStartDate = is_string($detail->item_start_date) ? \Carbon\Carbon::parse($detail->item_start_date) : $detail->item_start_date;
                                            $itemEndDate = is_string($detail->item_end_date) ? \Carbon\Carbon::parse($detail->item_end_date) : $detail->item_end_date;
                                        @endphp
                                        {{ $itemStartDate->format('d M Y') }} - {{ $itemEndDate->format('d M Y') }}
                                        @if($detail->rental_time)
                                            <span class="text-gray-600 font-normal">(Jam: {{ $detail->rental_time }})</span>
                                        @endif
                                    @else
                                        {{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($detail->danceService->description)
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Deskripsi</p>
                                <p class="text-gray-700">{{ $detail->danceService->description }}</p>
                            </div>
                            @endif
                        @elseif($detail->service_type == 'rias')
                            <div>
                                <p class="text-sm text-gray-600">Kategori</p>
                                <p class="font-semibold text-gray-900">{{ $detail->makeupService->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Booking</p>
                                <p class="font-semibold text-gray-900">{{ $detail->quantity }}x</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">📅 Jadwal</p>
                                <p class="font-semibold text-gray-900">
                                    @if($detail->item_start_date && $detail->item_end_date)
                                        @php
                                            $itemStartDate = is_string($detail->item_start_date) ? \Carbon\Carbon::parse($detail->item_start_date) : $detail->item_start_date;
                                            $itemEndDate = is_string($detail->item_end_date) ? \Carbon\Carbon::parse($detail->item_end_date) : $detail->item_end_date;
                                        @endphp
                                        {{ $itemStartDate->format('d M Y') }} - {{ $itemEndDate->format('d M Y') }}
                                        @if($detail->rental_time)
                                            <span class="text-gray-600 font-normal">(Jam: {{ $detail->rental_time }})</span>
                                        @endif
                                    @else
                                        {{ \Carbon\Carbon::parse($order->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($order->end_date)->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if($detail->makeupService->description)
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Deskripsi</p>
                                <p class="text-gray-700">{{ $detail->makeupService->description }}</p>
                            </div>
                            @endif
                        @endif
                    </div>

                    <!-- Price Calculation -->
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-blue-600">Subtotal</p>
                            <p class="text-sm text-gray-600">Rp {{ number_format((float)$detail->unit_price, 0, ',', '.') }} × {{ $detail->quantity }}</p>
                        </div>
                        <p class="text-xl font-bold text-blue-900">Rp {{ number_format((float)$detail->unit_price * $detail->quantity, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <p>Tidak ada jasa yang dipesan</p>
                </div>
            @endforelse

            <!-- Total Summary -->
            @if($order->orderDetails->count() > 0)
            <div class="mt-6 p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg border-2 border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">Total Pembayaran</p>
                        <p class="text-sm text-gray-500">{{ $order->orderDetails->count() }} jasa</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold text-red-600">Rp {{ number_format((float)$order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Alamat Pengiriman -->
        @if($order->address)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">📍 Alamat Pengiriman</h2>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border-l-4 border-blue-500">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Nama Penerima</p>
                            <p class="font-semibold text-gray-900">{{ $order->address->recipient_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">No. Telepon</p>
                            <p class="text-gray-900">{{ $order->address->phone_number }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Alamat Lengkap</p>
                        <p class="text-gray-900 leading-relaxed">
                            {{ $order->address->street_address }}<br>
                            <span class="text-sm">{{ $order->address->city }}, {{ $order->address->province }} {{ $order->address->postal_code }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Informasi Pembayaran -->
        @if($order->transaction)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">💳 Informasi Pembayaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Kode Transaksi</p>
                        <p class="font-mono text-gray-900 bg-gray-100 px-3 py-2 rounded text-sm">{{ $order->transaction->transaction_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Metode Pembayaran</p>
                        <p class="text-gray-900 capitalize font-medium">{{ str_replace('_', ' ', $order->transaction->payment_method) }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Status Pembayaran</p>
                        <div class="mt-1">
                            @if($order->transaction->pg_status == 'settlement')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">✓ Settlement</span>
                            @elseif($order->transaction->pg_status == 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">⏳ Pending</span>
                            @elseif($order->transaction->pg_status == 'expire')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">✗ Expired</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">✗ {{ ucfirst($order->transaction->pg_status) }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Tanggal Pembayaran</p>
                        <p class="text-gray-900">{{ $order->transaction->paid_at ? $order->transaction->paid_at->format('d M Y H:i') : 'Belum dibayar' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar - Per-Item Return Status Management -->
    <div class="space-y-6">
        <!-- Per-Item Return Status Management -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Status Pengembalian Per-Item</h3>
            
            @if($order->status == 'expired')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <p class="text-red-800 font-medium">✗ Order Expired</p>
                    <p class="text-red-700 text-sm mt-1">Order sudah kadaluarsa dan tidak dapat diubah</p>
                </div>
            @elseif($order->transaction && $order->transaction->pg_status == 'pending')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-800 font-medium">⏳ Transaksi Pending</p>
                    <p class="text-yellow-700 text-sm mt-1">Menunggu konfirmasi pembayaran dari customer</p>
                </div>
            @else
                @php
                    $hasDanceService = $order->orderDetails->contains(fn($detail) => $detail->service_type === 'tari');
                @endphp
                
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    {{-- Per-Item Return Status Cards --}}
                    <div class="space-y-3 mb-6">
                        @foreach($order->orderDetails as $index => $detail)
                            @php
                                $itemEndDate = $detail->item_end_date ?? $order->end_date;
                                $itemEndDateCarbon = \Carbon\Carbon::parse($itemEndDate);
                                $isOverdue = now() > $itemEndDateCarbon->endOfDay();
                                $daysUntilDeadline = now()->diffInDays($itemEndDateCarbon, false);
                                $itemReturnStatus = $detail->item_return_status ?? 'belum';
                                $itemReturnDate = $detail->item_return_date;
                            @endphp
                            
                            <div class="border-2 rounded-lg p-3 transition
                                @if($itemReturnStatus == 'sudah') bg-green-50 border-green-300
                                @elseif($itemReturnStatus == 'terlambat') bg-orange-50 border-orange-300
                                @elseif($isOverdue) bg-red-50 border-red-300
                                @else bg-gray-50 border-gray-300
                                @endif
                            ">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1 mb-1 flex-wrap">
                                            @if($detail->service_type == 'kostum')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">👗 Kostum</span>
                                            @elseif($detail->service_type == 'tari')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-pink-100 text-pink-800">💃 Tari</span>
                                            @elseif($detail->service_type == 'rias')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">💄 Rias</span>
                                            @endif
                                            
                                            @if($itemReturnStatus == 'sudah')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">✓ Dikembalikan</span>
                                            @elseif($itemReturnStatus == 'terlambat')
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">⏱️ Terlambat</span>
                                            @elseif($isOverdue)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">⚠️ Overdue</span>
                                            @endif
                                        </div>
                                        
                                        <p class="font-semibold text-gray-900 text-sm truncate">
                                            @if($detail->service_type == 'kostum')
                                                {{ $detail->costume->costume_name ?? 'Costume' }}
                                            @elseif($detail->service_type == 'tari')
                                                {{ $detail->danceService->package_name ?? 'Dance Service' }}
                                            @elseif($detail->service_type == 'rias')
                                                {{ $detail->makeupService->package_name ?? 'Makeup Service' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mb-2 pb-2 border-b border-gray-300">
                                    <p class="text-xs text-gray-600 mb-1">Deadline Pengembalian</p>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $itemEndDateCarbon->format('d M Y') }}
                                        @if(!$hasDanceService && $detail->item_end_date)
                                            <span class="text-gray-600 font-normal text-xs">{{ $itemEndDateCarbon->format('H:i') }}</span>
                                        @endif
                                    </p>
                                    @if($isOverdue && $itemReturnStatus == 'belum')
                                        <p class="text-xs text-red-600 mt-1">⚠️ {{ abs($daysUntilDeadline) }} hari terlambat</p>
                                    @elseif(!$isOverdue && $itemReturnStatus == 'belum')
                                        <p class="text-xs {{ $daysUntilDeadline <= 1 ? 'text-orange-600' : 'text-gray-600' }} mt-1">
                                            @if($daysUntilDeadline == 0)
                                                ⏰ Hari ini deadline
                                            @elseif($daysUntilDeadline == 1)
                                                ⏰ Besok deadline
                                            @else
                                                {{ $daysUntilDeadline }} hari lagi
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 p-1.5 border rounded cursor-pointer text-sm {{ $itemReturnStatus == 'belum' ? 'bg-white border-gray-400' : 'bg-gray-50 border-gray-200' }}" {{ $isOverdue && $itemReturnStatus != 'belum' ? 'disabled' : '' }}>
                                        <input type="radio" name="item_return_status[{{ $index }}]" value="belum" {{ $itemReturnStatus == 'belum' ? 'checked' : '' }} class="w-3 h-3" onchange="toggleItemDateField({{ $index }}, false)">
                                        <span class="font-medium text-gray-900">Belum</span>
                                    </label>
                                    
                                    <label class="flex items-center gap-2 p-1.5 border rounded cursor-pointer text-sm {{ $itemReturnStatus == 'sudah' ? 'bg-white border-green-400' : 'bg-gray-50 border-gray-200' }}" {{ $isOverdue && $itemReturnStatus != 'sudah' ? '' : '' }}>
                                        <input type="radio" name="item_return_status[{{ $index }}]" value="sudah" {{ $itemReturnStatus == 'sudah' ? 'checked' : '' }} class="w-3 h-3" onchange="toggleItemDateField({{ $index }}, false)">
                                        <span class="font-medium text-gray-900">✓ Sudah</span>
                                    </label>
                                    
                                    @if(!$hasDanceService)
                                    <label class="flex items-center gap-2 p-1.5 border rounded cursor-pointer text-sm {{ $itemReturnStatus == 'terlambat' ? 'bg-white border-orange-400' : 'bg-gray-50 border-gray-200' }}">
                                        <input type="radio" name="item_return_status[{{ $index }}]" value="terlambat" {{ $itemReturnStatus == 'terlambat' ? 'checked' : '' }} class="w-3 h-3" onchange="toggleItemDateField({{ $index }}, true)">
                                        <span class="font-medium text-gray-900">⏱️ Terlambat</span>
                                    </label>
                                    @endif
                                </div>
                                
                                @if(!$hasDanceService)
                                <div id="itemDateField_{{ $index }}" style="display: {{ $itemReturnStatus == 'terlambat' ? 'block' : 'none' }}" class="mt-2 pt-2 border-t border-gray-300">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Tgl Kembali Aktual</label>
                                    <input type="date" name="item_return_date[{{ $index }}]" class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500" value="{{ $itemReturnDate ? \Carbon\Carbon::parse($itemReturnDate)->format('Y-m-d') : '' }}">
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition font-medium text-sm">
                        💾 Update Semua Item
                    </button>
                </form>
                
                <script>
                function toggleItemDateField(index, show) {
                    const field = document.getElementById('itemDateField_' + index);
                    if (field) {
                        field.style.display = show ? 'block' : 'none';
                    }
                }
                </script>
            @endif
        </div>

        <!-- Per-Item Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Timeline Per-Item</h3>
            
            {{-- Order Level Timeline --}}
            <div class="mb-4 pb-4 border-b border-gray-200">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-3">Order Timeline</p>
                <div class="space-y-2">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-7 w-7 rounded-full bg-blue-100 text-blue-600 text-xs">📝</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-600">Order Created</p>
                            <p class="font-semibold text-gray-900 text-sm">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-7 w-7 rounded-full bg-gray-100 text-gray-600 text-xs">🔄</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-600">Last Updated</p>
                            <p class="font-semibold text-gray-900 text-sm">{{ $order->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Per-Item Timeline --}}
            <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Jadwal Per-Item</p>
            <div class="space-y-2">
                @foreach($order->orderDetails as $detail)
                    @php
                        $itemStartDate = $detail->item_start_date ?? $order->start_date;
                        $itemEndDate = $detail->item_end_date ?? $order->end_date;
                        $itemStartDateCarbon = \Carbon\Carbon::parse($itemStartDate);
                        $itemEndDateCarbon = \Carbon\Carbon::parse($itemEndDate);
                    @endphp
                    
                    <div class="border rounded p-2 text-xs
                        @if($detail->service_type == 'kostum') bg-purple-50 border-purple-200
                        @elseif($detail->service_type == 'tari') bg-pink-50 border-pink-200
                        @else bg-red-50 border-red-200
                        @endif
                    ">
                        <p class="font-semibold text-gray-900 mb-1 truncate">
                            @if($detail->service_type == 'kostum')
                                👗 {{ $detail->costume->costume_name ?? 'Costume' }}
                            @elseif($detail->service_type == 'tari')
                                💃 {{ $detail->danceService->package_name ?? 'Dance' }}
                            @else
                                💄 {{ $detail->makeupService->package_name ?? 'Makeup' }}
                            @endif
                        </p>
                        <div class="space-y-1">
                            <div class="text-gray-700">
                                <span class="text-gray-600">🚀 Mulai:</span> {{ $itemStartDateCarbon->format('d M Y') }}
                                @if($detail->rental_time)
                                    <span class="text-gray-600 ml-1">{{ $detail->rental_time }}</span>
                                @endif
                            </div>
                            <div class="text-gray-700">
                                <span class="text-gray-600">⏳ Selesai:</span> {{ $itemEndDateCarbon->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Informasi Customer -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">👤 Informasi Customer</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Nama Lengkap</p>
                    <p class="font-semibold text-gray-900">{{ $order->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-medium">Email</p>
                    <p class="text-gray-900 text-sm">{{ $order->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-medium">No. Telepon</p>
                    <p class="text-gray-900">{{ $order->user->phone_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-medium">Tanggal Order</p>
                    <p class="text-gray-900">{{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">Catatan Customer</h3>
            <p class="text-sm text-blue-800">{{ $order->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
