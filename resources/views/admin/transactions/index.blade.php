@extends('layouts.admin')

@section('title', 'Transaksi')
@section('page-title', 'Manajemen Transaksi')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-medium text-gray-700">Daftar Transaksi</h3>
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

<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode transaksi, order ID, atau customer..." class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 md:col-span-2">
            
            <select name="status" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="settlement" {{ request('status') == 'settlement' ? 'selected' : '' }}>Settlement</option>
                <option value="expire" {{ request('status') == 'expire' ? 'selected' : '' }}>Expire</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Dari Tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Sampai Tanggal">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Cari
                </button>
                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.transactions.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalTransactions }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Pendapatan</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Success</p>
                <p class="text-2xl font-bold text-blue-600">{{ $successTransactions }}</p>
            </div>
            <div class="bg-blue-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $pendingTransactions }}</p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Transaksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-blue-600">
                        {{ $transaction->transaction_code }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <a href="{{ route('admin.orders.show', $transaction->order) }}" class="text-blue-600 hover:underline">
                            #{{ $transaction->order->order_code }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $transaction->order->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $transaction->order->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ ucfirst($transaction->payment_method) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($transaction->pg_status == 'settlement')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Settlement</span>
                        @elseif($transaction->pg_status == 'pending')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Expire</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($transaction->paid_at)
                        {{ $transaction->paid_at->format('d M Y H:i') }}
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2">Tidak ada data transaksi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $transactions->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $transactions->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $transactions->total() }}</span> entries
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if ($transactions->onFirstPage())
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max($transactions->currentPage() - 2, 1);
                    $end = min($start + 4, $transactions->lastPage());
                    $start = max($end - 4, 1);
                @endphp

                @if($start > 1)
                    <a href="{{ $transactions->url(1) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $transactions->currentPage())
                        <span class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg font-medium">{{ $i }}</span>
                    @else
                        <a href="{{ $transactions->url($i) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $transactions->lastPage())
                    @if($end < $transactions->lastPage() - 1)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                    <a href="{{ $transactions->url($transactions->lastPage()) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $transactions->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if ($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next</a>
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
    const url = "{{ route('admin.transactions.export.excel') }}" + '?' + params.toString();
    window.location.href = url;
}

function exportPdf() {
    const params = new URLSearchParams(window.location.search);
    const url = "{{ route('admin.transactions.export.pdf') }}" + '?' + params.toString();
    window.open(url, '_blank');
}
</script>
@endpush
