@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-red-800">Users</a>
        <span>/</span>
        <span>Detail</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail User</h1>
</div>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- User Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-20 w-20 rounded-full bg-red-800 flex items-center justify-center text-white text-3xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div>
                    <p class="text-sm text-gray-600">No. Telepon</p>
                    <p class="font-medium text-gray-900">{{ $user->phone_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Role</p>
                    <div class="mt-1">
                        @if($user->role == 'admin')
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded font-medium">Admin</span>
                        @else
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-medium">Customer</span>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Bergabung Sejak</p>
                    <p class="font-medium text-gray-900">{{ $user->created_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Terakhir Update</p>
                    <p class="font-medium text-gray-900">{{ $user->updated_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Riwayat Pesanan -->
        @if($user->role == 'customer')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pesanan</h3>
            
            @if($user->orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($user->orders->take(5) as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-blue-600">
                                <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">
                                    #{{ $order->order_code }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $order->start_date ? $order->start_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($order->status == 'completed')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Selesai</span>
                                @elseif($order->status == 'confirmed')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Dikonfirmasi</span>
                                @elseif($order->status == 'paid')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Dibayar</span>
                                @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($user->orders->count() > 5)
            <p class="text-sm text-gray-500 mt-3">Menampilkan 5 pesanan terakhir dari {{ $user->orders->count() }} total pesanan</p>
            @endif
            @else
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2">Belum ada pesanan</p>
            </div>
            @endif
        </div>

        <!-- Review yang Ditulis -->
        @if($user->reviews->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Review yang Ditulis</h3>
            <div class="space-y-3">
                @foreach($user->reviews->take(3) as $review)
                <div class="border-b pb-3">
                    <div class="flex items-center gap-2 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            @endif
                        @endfor
                        <span class="text-xs text-gray-500">{{ $review->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="text-sm text-gray-900">{{ $review->comment }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Order: <a href="{{ route('admin.orders.show', $review->order) }}" class="text-blue-600 hover:underline">#{{ $review->order->order_code }}</a>
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Statistik Customer -->
        @if($user->role == 'customer')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Total Pesanan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $user->orders->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($user->orders->sum('total_price'), 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Review Ditulis</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $user->reviews->count() }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Change Role -->
        @if($user->id != auth()->id())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ubah Role</h3>
            <form action="{{ route('admin.users.update-role', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <select name="role" class="w-full border rounded-lg px-4 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="customer" {{ $user->role == 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                    Update Role
                </button>
            </form>
        </div>

        <!-- Delete User -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-2">Zona Berbahaya</h3>
            <p class="text-sm text-red-700 mb-4">Aksi ini tidak dapat dibatalkan</p>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini? Semua data terkait akan ikut terhapus!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    Hapus User
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
