@extends('layouts.admin')

@section('title', 'Tambah Jasa Rias')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.makeup-services.index') }}" class="hover:text-red-800">Jasa Rias</a>
        <span>/</span>
        <span>Tambah</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Jasa Rias</h1>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.makeup-services.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Paket -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Paket <span class="text-red-600">*</span>
                </label>
                <input type="text" name="package_name" value="{{ old('package_name') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('package_name') border-red-500 @enderror"
                    placeholder="Contoh: Paket Rias Wisuda Premium">
                @error('package_name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Total Slot Tersedia -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Total Slot Tersedia
                </label>
                <input type="number" name="total_slots" value="{{ old('total_slots', 10) }}" 
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('total_slots') border-red-500 @enderror"
                    placeholder="10">
                @error('total_slots')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori <span class="text-red-600">*</span>
                </label>
                <select name="category" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('category') border-red-500 @enderror">
                    <option value="">Pilih Kategori</option>
                    <option value="SD" {{ old('category') == 'SD' ? 'selected' : '' }}>SD (Sekolah Dasar)</option>
                    <option value="SMP" {{ old('category') == 'SMP' ? 'selected' : '' }}>SMP (Sekolah Menengah Pertama)</option>
                    <option value="SMA" {{ old('category') == 'SMA' ? 'selected' : '' }}>SMA (Sekolah Menengah Atas)</option>
                    <option value="Wisuda" {{ old('category') == 'Wisuda' ? 'selected' : '' }}>Wisuda</option>
                    <option value="Acara Umum" {{ old('category') == 'Acara Umum' ? 'selected' : '' }}>Acara Umum</option>
                </select>
                @error('category')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Harga -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Harga (Rp) <span class="text-red-600">*</span>
                </label>
                <input type="number" name="price" value="{{ old('price') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('price') border-red-500 @enderror"
                    placeholder="150000" min="0">
                @error('price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi
                </label>
                <textarea name="description" rows="4" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('description') border-red-500 @enderror"
                    placeholder="Deskripsi detail tentang paket jasa rias ini...">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Gambar -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Foto Jasa Rias
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none">
                                <span>Upload file</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
                            </label>
                            <p class="pl-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG maksimal 2MB</p>
                    </div>
                </div>
                @error('image')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                <!-- Image Preview -->
                <div id="imagePreview" class="mt-4 hidden">
                    <img src="" alt="Preview" class="max-h-64 mx-auto rounded-lg shadow-md">
                </div>
            </div>

            <!-- Status Ketersediaan -->
            <div class="md:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="text-sm font-medium text-gray-700">Tersedia untuk Pemesanan</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 mt-6 pt-6 border-t">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                Simpan
            </button>
            <a href="{{ route('admin.makeup-services.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
@endsection
