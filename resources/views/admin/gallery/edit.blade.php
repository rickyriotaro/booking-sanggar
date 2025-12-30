@extends('layouts.admin')

@section('title', 'Edit Foto')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.gallery.index') }}" class="hover:text-red-800">Galeri</a>
        <span>/</span>
        <span>Edit</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Foto</h1>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.gallery.update', $gallery) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Current Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Saat Ini</label>
                <img src="{{ Storage::url($gallery->image_path) }}" 
                     alt="{{ $gallery->title }}" 
                     class="max-h-64 rounded-lg">
            </div>

            <!-- New Image Upload (Optional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ganti Foto (Opsional)
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none">
                                <span>Upload file baru</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                    </div>
                </div>
                @error('image')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                <!-- New Image Preview -->
                <div id="imagePreview" class="mt-4 hidden">
                    <p class="text-sm text-gray-600 mb-2">Preview Foto Baru:</p>
                    <img src="" alt="Preview" class="max-h-64 rounded-lg">
                </div>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Foto <span class="text-red-600">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $gallery->title) }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('title') border-red-500 @enderror"
                    placeholder="Deskripsi singkat foto..."
                    required>
                @error('title')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori <span class="text-red-600">*</span>
                </label>
                <select name="category" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('category') border-red-500 @enderror"
                    required>
                    <option value="">Pilih Kategori</option>
                    <option value="Jasa Tari" {{ old('category', $gallery->category) == 'Jasa Tari' ? 'selected' : '' }}>Jasa Tari</option>
                    <option value="Jasa Rias" {{ old('category', $gallery->category) == 'Jasa Rias' ? 'selected' : '' }}>Jasa Rias</option>
                    <option value="Kostum" {{ old('category', $gallery->category) == 'Kostum' ? 'selected' : '' }}>Kostum</option>
                    <option value="Umum" {{ old('category', $gallery->category) == 'Umum' ? 'selected' : '' }}>Umum</option>
                </select>
                @error('category')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Pilih kategori untuk memudahkan pengelompokan foto di aplikasi</p>
            </div>
        </div>

        <div class="flex gap-3 mt-6 pt-6 border-t">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                Update
            </button>
            <a href="{{ route('admin.gallery.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>

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
@endsection
