@extends('layouts.admin')

@section('title', 'Kelola Pesanan')
@section('page-title', 'Manajemen Pemesanan')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-medium text-gray-700">Daftar Pemesanan</h3>
    </div>
    <div class="flex gap-2">
        <button onclick="exportExcel()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </button>
        <button onclick="exportPdf()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Export PDF
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari order ID, customer, atau email..." class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 md:col-span-2">
            
            <select name="status" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Dari Tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Sampai Tanggal">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Cari
                </button>
                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Acara</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengembalian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-blue-600">
                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">
                            #{{ $order->order_code }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $order->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $order->start_date ? \Carbon\Carbon::parse($order->start_date)->format('d M Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($order->status == 'pending')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                        @elseif($order->status == 'paid')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Lunas</span>
                        @elseif($order->status == 'confirmed')
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Confirmed</span>
                        @elseif($order->status == 'completed')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Completed</span>
                        @elseif($order->status == 'expired')
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Expired</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Cancelled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($order->transaction && $order->transaction->pg_status == 'expired')
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Gagal</span>
                        @elseif($order->return_status == 'belum')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Belum</span>
                        @elseif($order->return_status == 'terlambat')
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Terlambat</span>
                        @else
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Sudah</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2">Tidak ada data pesanan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $orders->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $orders->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $orders->total() }}</span> entries
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if ($orders->onFirstPage())
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max($orders->currentPage() - 2, 1);
                    $end = min($start + 4, $orders->lastPage());
                    $start = max($end - 4, 1);
                @endphp

                @if($start > 1)
                    <a href="{{ $orders->url(1) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $orders->currentPage())
                        <span class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg font-medium">{{ $i }}</span>
                    @else
                        <a href="{{ $orders->url($i) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $orders->lastPage())
                    @if($end < $orders->lastPage() - 1)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                    <a href="{{ $orders->url($orders->lastPage()) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $orders->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if ($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next</a>
                @else
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('admin.orders.export.excel') }}" + '?' + params.toString();
    window.location.href = url;
}

function exportPdf() {
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('admin.orders.export.pdf') }}" + '?' + params.toString();
    window.open(url, '_blank');
}
</script>
@endpush
