@extends('layouts.admin')

@section('title', 'Jasa Tari')
@section('page-title', 'Manajemen Jasa Tari')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-medium text-gray-700">Daftar Paket Jasa Tari</h3>
    </div>
    <a href="{{ route('admin.dance-services.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Jasa Tari
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('admin.dance-services.index') }}" class="flex gap-2">
            <select name="dance_type" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Semua Tipe Tarian</option>
                <option value="Tradisional" {{ request('dance_type') == 'Tradisional' ? 'selected' : '' }}>Tradisional</option>
                <option value="Modern" {{ request('dance_type') == 'Modern' ? 'selected' : '' }}>Modern</option>
                <option value="Kontemporer" {{ request('dance_type') == 'Kontemporer' ? 'selected' : '' }}>Kontemporer</option>
                <option value="Kreasi Baru" {{ request('dance_type') == 'Kreasi Baru' ? 'selected' : '' }}>Kreasi Baru</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama paket atau tipe tarian..." class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Cari
            </button>
            @if(request('search') || request('dance_type'))
            <a href="{{ route('admin.dance-services.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Tarian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Penari</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Aktif</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($danceServices as $index => $service)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($service->image_path)
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->package_name }}" class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $service->package_name }}</div>
                        @if($service->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($service->description, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $service->dance_type }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                            {{ $service->number_of_dancers }} Penari
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        Rp {{ number_format($service->price, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($service->isAvailable()) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $service->getAvailabilityStatus() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="font-medium">{{ $service->orderDetails->filter(function($detail) {
                            return in_array($detail->order->status, ['paid', 'completed']) && 
                                   in_array($detail->item_return_status ?? 'belum', ['belum', null]);
                        })->count() }}</span> aktif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.dance-services.show', $service) }}" class="text-blue-600 hover:text-blue-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.dance-services.edit', $service) }}" class="text-yellow-600 hover:text-yellow-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form id="delete-dance-{{ $service->id }}" action="{{ route('admin.dance-services.destroy', $service) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-dance-{{ $service->id }}', 'paket {{ $service->package_name }}')" class="text-red-600 hover:text-red-900">
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
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2">Tidak ada data jasa tari</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($danceServices->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $danceServices->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $danceServices->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $danceServices->total() }}</span> entries
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if ($danceServices->onFirstPage())
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $danceServices->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max($danceServices->currentPage() - 2, 1);
                    $end = min($start + 4, $danceServices->lastPage());
                    $start = max($end - 4, 1);
                @endphp

                @if($start > 1)
                    <a href="{{ $danceServices->url(1) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $danceServices->currentPage())
                        <span class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg font-medium">{{ $i }}</span>
                    @else
                        <a href="{{ $danceServices->url($i) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $danceServices->lastPage())
                    @if($end < $danceServices->lastPage() - 1)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                    <a href="{{ $danceServices->url($danceServices->lastPage()) }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $danceServices->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if ($danceServices->hasMorePages())
                    <a href="{{ $danceServices->nextPageUrl() }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next</a>
                @else
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
