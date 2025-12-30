@extends('layouts.admin')

@section('title', 'Galeri')
@section('page-title', 'Manajemen Galeri')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h3 class="text-lg font-medium text-gray-700">Daftar Foto Galeri</h3>
    </div>
    <a href="{{ route('admin.gallery.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Upload Foto
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('admin.gallery.index') }}" class="flex gap-2">
            <select name="category" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Semua Kategori</option>
                <option value="Jasa Tari" {{ request('category') == 'Jasa Tari' ? 'selected' : '' }}>Jasa Tari</option>
                <option value="Jasa Rias" {{ request('category') == 'Jasa Rias' ? 'selected' : '' }}>Jasa Rias</option>
                <option value="Kostum" {{ request('category') == 'Kostum' ? 'selected' : '' }}>Kostum</option>
                <option value="Umum" {{ request('category') == 'Umum' ? 'selected' : '' }}>Umum</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul foto..." class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Cari
            </button>
            @if(request('search') || request('category'))
            <a href="{{ route('admin.gallery.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                Reset
            </a>
            @endif
        </form>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($galleries as $gallery)
            <div class="group bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                <div class="relative">
                    <img src="{{ Storage::url($gallery->image_path) }}" 
                         alt="{{ $gallery->title }}" 
                         class="w-full h-48 object-cover">
                    
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-opacity duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.gallery.edit', $gallery) }}" class="bg-white hover:bg-gray-100 text-gray-900 p-2 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form id="delete-gallery-{{ $gallery->id }}" action="{{ route('admin.gallery.destroy', $gallery) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-gallery-{{ $gallery->id }}', 'foto {{ $gallery->title }}')" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-900 truncate flex-1">{{ $gallery->title }}</p>
                        @if($gallery->category == 'Jasa Tari')
                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded ml-2">Jasa Tari</span>
                        @elseif($gallery->category == 'Jasa Rias')
                            <span class="bg-pink-100 text-pink-800 text-xs font-medium px-2 py-1 rounded ml-2">Jasa Rias</span>
                        @elseif($gallery->category == 'Kostum')
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded ml-2">Kostum</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded ml-2">Umum</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500">{{ $gallery->created_at->format('d M Y') }}</p>
                    <p class="text-xs text-gray-500">oleh {{ $gallery->uploader->name }}</p>
                </div>
            </div>
            @empty
            <div class="col-span-4 py-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="mt-2">Belum ada foto di galeri</p>
            </div>
            @endforelse
        </div>
    </div>

    @if($galleries->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $galleries->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $galleries->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $galleries->total() }}</span> entries
            </div>
            <div class="flex items-center gap-2">
                {{-- Previous Button --}}
                @if ($galleries->onFirstPage())
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $galleries->previousPageUrl() }}&category={{ request('category') }}&search={{ request('search') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max($galleries->currentPage() - 2, 1);
                    $end = min($start + 4, $galleries->lastPage());
                    $start = max($end - 4, 1);
                @endphp

                @if($start > 1)
                    <a href="{{ $galleries->url(1) }}&category={{ request('category') }}&search={{ request('search') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $galleries->currentPage())
                        <span class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg font-medium">{{ $i }}</span>
                    @else
                        <a href="{{ $galleries->url($i) }}&category={{ request('category') }}&search={{ request('search') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $galleries->lastPage())
                    @if($end < $galleries->lastPage() - 1)
                        <span class="px-2 text-gray-500">...</span>
                    @endif
                    <a href="{{ $galleries->url($galleries->lastPage()) }}&category={{ request('category') }}&search={{ request('search') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $galleries->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if ($galleries->hasMorePages())
                    <a href="{{ $galleries->nextPageUrl() }}&category={{ request('category') }}&search={{ request('search') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next</a>
                @else
                    <span class="px-4 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
