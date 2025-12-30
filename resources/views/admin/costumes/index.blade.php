@extends('layouts.admin')

@section('title', 'Daftar Kostum')
@section('page-title', 'Manajemen Kostum')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-medium text-gray-700">Daftar Kostum Tersedia</h3>
    </div>
    <a href="{{ route('admin.costumes.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Kostum
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('admin.costumes.index') }}" class="flex gap-2">
            <select name="size" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Semua Ukuran</option>
                <option value="XS" {{ request('size') == 'XS' ? 'selected' : '' }}>XS</option>
                <option value="S" {{ request('size') == 'S' ? 'selected' : '' }}>S</option>
                <option value="M" {{ request('size') == 'M' ? 'selected' : '' }}>M</option>
                <option value="L" {{ request('size') == 'L' ? 'selected' : '' }}>L</option>
                <option value="XL" {{ request('size') == 'XL' ? 'selected' : '' }}>XL</option>
                <option value="XXL" {{ request('size') == 'XXL' ? 'selected' : '' }}>XXL</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kostum..." class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Cari
            </button>
            @if(request('search') || request('size'))
            <a href="{{ route('admin.costumes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                Reset
            </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kostum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Sewa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Aktif</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($costumes as $costume)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($costume->image_path)
                            <img src="{{ asset('storage/' . $costume->image_path) }}" alt="{{ $costume->costume_name }}" class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $costume->costume_name }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($costume->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $costume->size ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                        Rp {{ number_format($costume->rental_price, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if(($costume->sisa_stok_tersedia ?? $costume->stock) <= 3) bg-red-100 text-red-800
                            @elseif(($costume->sisa_stok_tersedia ?? $costume->stock) <= 10) bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ $costume->sisa_stok_tersedia ?? $costume->stock }} unit
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($costume->isAvailable()) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $costume->getAvailabilityStatus() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center">
                            <span class="font-medium">{{ $costume->orderDetails->filter(function($detail) {
                                return in_array($detail->order->status, ['paid', 'completed']) && 
                                       in_array($detail->item_return_status ?? 'belum', ['belum', null]);
                            })->count() }}</span>
                            <span class="ml-2 text-gray-500">aktif</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.costumes.show', $costume) }}" class="text-blue-600 hover:text-blue-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.costumes.edit', $costume) }}" class="text-yellow-600 hover:text-yellow-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form id="delete-costume-{{ $costume->id }}" action="{{ route('admin.costumes.destroy', $costume) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-costume-{{ $costume->id }}', 'kostum {{ $costume->costume_name }}')" class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        Belum ada data kostum
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($costumes->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $costumes->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $costumes->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $costumes->total() }}</span> entries
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if ($costumes->onFirstPage())
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $costumes->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max($costumes->currentPage() - 2, 1);
                    $end = min($start + 4, $costumes->lastPage());
                    $start = max($end - 4, 1);
                @endphp

                @if($start > 1)
                    <a href="{{ $costumes->url(1) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $costumes->currentPage())
                        <span class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg font-medium">{{ $i }}</span>
                    @else
                        <a href="{{ $costumes->url($i) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $costumes->lastPage())
                    @if($end < $costumes->lastPage() - 1)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                    <a href="{{ $costumes->url($costumes->lastPage()) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $costumes->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if ($costumes->hasMorePages())
                    <a href="{{ $costumes->nextPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next</a>
                @else
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
