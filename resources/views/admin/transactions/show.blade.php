@extends('layouts.admin')

@section('title', 'Detail Transaksi')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.transactions.index') }}" class="hover:text-red-800">Transaksi</a>
        <span>/</span>
        <span>Detail</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail Transaksi</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Informasi Transaksi -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Transaksi</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Kode Transaksi</p>
                    <p class="font-bold text-gray-900 text-lg">{{ $transaction->transaction_code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <div class="mt-1">
                        @if($transaction->pg_status == 'settlement')
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-medium">Settlement</span>
                        @elseif($transaction->pg_status == 'pending')
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm font-medium">Pending</span>
                        @else
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded text-sm font-medium">{{ ucfirst($transaction->pg_status) }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Metode Pembayaran</p>
                    <p class="font-medium text-gray-900">{{ ucfirst($transaction->payment_method) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Jumlah</p>
                    <p class="font-bold text-green-600 text-lg">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                </div>
                @if($transaction->paid_at)
                <div>
                    <p class="text-sm text-gray-600">Tanggal Bayar</p>
                    <p class="font-medium text-gray-900">{{ $transaction->paid_at->format('d M Y H:i:s') }}</p>
                </div>
                @endif
                @if($transaction->pg_transaction_id)
                <div>
                    <p class="text-sm text-gray-600">Payment Gateway ID</p>
                    <p class="font-medium text-gray-900">{{ $transaction->pg_transaction_id }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Informasi Pesanan -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Informasi Pesanan</h2>
                <a href="{{ route('admin.orders.show', $transaction->order) }}" class="text-blue-600 hover:underline text-sm">
                    Lihat Detail Pesanan →
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Order Code</p>
                    <p class="font-medium text-gray-900">#{{ $transaction->order->order_code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status Pesanan</p>
                    <div class="mt-1">
                        @if($transaction->order->status == 'completed')
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Completed</span>
                        @elseif($transaction->order->status == 'confirmed')
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Confirmed</span>
                        @elseif($transaction->order->status == 'paid')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Paid</span>
                        @else
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ ucfirst($transaction->order->status) }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Customer</p>
                    <p class="font-medium text-gray-900">{{ $transaction->order->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $transaction->order->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tanggal Acara</p>
                    <p class="font-medium text-gray-900">{{ $transaction->order->event_date ? $transaction->order->event_date->format('d M Y') : '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Detail Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Item Pesanan</h2>
            <div class="space-y-3">
                @foreach($transaction->order->orderDetails as $detail)
                <div class="flex justify-between items-start border-b pb-3">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $detail->service->package_name ?? $detail->service->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ class_basename($detail->service_type) }}
                            @if($detail->service_type == 'App\\Models\\Costume')
                            - {{ $detail->start_date->format('d M Y') }} s/d {{ $detail->end_date->format('d M Y') }}
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">Qty: {{ $detail->quantity }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">Rp {{ number_format((float)$detail->unit_price, 0, ',', '.') }}</p>
                        <p class="text-sm font-bold text-gray-900">Rp {{ number_format((float)$detail->unit_price * $detail->quantity, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
                
                <div class="flex justify-between items-center pt-3 text-lg font-bold">
                    <span>Total</span>
                    <span class="text-red-800">Rp {{ number_format((float)$transaction->order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Transaksi Dibuat</p>
                        <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                @if($transaction->paid_at)
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Pembayaran Diterima</p>
                        <p class="text-xs text-gray-500">{{ $transaction->paid_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif

                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Terakhir Update</p>
                        <p class="text-xs text-gray-500">{{ $transaction->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($transaction->pg_status == 'pending')
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800 font-medium mb-2">Transaksi Pending</p>
            <p class="text-xs text-yellow-700">Menunggu konfirmasi pembayaran dari customer</p>
        </div>
        @endif
    </div>
</div>
@endsection
