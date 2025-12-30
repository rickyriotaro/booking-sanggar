@extends('layouts.admin')

@section('title', 'Tambah Kostum')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.costumes.index') }}" class="hover:text-red-800">Kostum</a>
        <span>/</span>
        <span>Tambah</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Kostum</h1>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.costumes.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Kostum -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kostum <span class="text-red-600">*</span>
                </label>
                <input type="text" name="costume_name" value="{{ old('costume_name') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('costume_name') border-red-500 @enderror"
                    placeholder="Contoh: Kostum Adat Jawa">
                @error('costume_name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Stok -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Stok <span class="text-red-600">*</span>
                </label>
                <input type="number" name="stock" value="{{ old('stock') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('stock') border-red-500 @enderror"
                    placeholder="10" min="0">
                @error('stock')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Harga Sewa -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Harga Sewa (Rp) <span class="text-red-600">*</span>
                </label>
                <input type="number" name="rental_price" value="{{ old('rental_price') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('rental_price') border-red-500 @enderror"
                    placeholder="150000" min="0">
                @error('rental_price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ukuran -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ukuran Tersedia <span class="text-red-600">*</span>
                </label>
                <div class="space-y-2">
                    <label class="inline-flex items-center mr-4">
                        <input type="checkbox" name="sizes[]" value="All Size" 
                            {{ is_array(old('sizes')) && in_array('All Size', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500" 
                            onchange="toggleAllSize(this)">
                        <span class="ml-2 text-sm">All Size</span>
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2" id="sizeOptions">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="XS" 
                            {{ is_array(old('sizes')) && in_array('XS', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">XS</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="S" 
                            {{ is_array(old('sizes')) && in_array('S', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">S</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="M" 
                            {{ is_array(old('sizes')) && in_array('M', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">M</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="L" 
                            {{ is_array(old('sizes')) && in_array('L', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">L</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="XL" 
                            {{ is_array(old('sizes')) && in_array('XL', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">XL</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="XXL" 
                            {{ is_array(old('sizes')) && in_array('XXL', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">XXL</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="sizes[]" value="XXXL" 
                            {{ is_array(old('sizes')) && in_array('XXXL', old('sizes')) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 size-checkbox">
                        <span class="ml-2 text-sm">XXXL</span>
                    </label>
                </div>
                @error('sizes')
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
                    placeholder="Deskripsi detail tentang kostum ini...">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Gambar -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Foto Kostum
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
            <a href="{{ route('admin.costumes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg">
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

function toggleAllSize(checkbox) {
    const sizeCheckboxes = document.querySelectorAll('.size-checkbox');
    if (checkbox.checked) {
        sizeCheckboxes.forEach(cb => {
            cb.checked = false;
            cb.disabled = true;
        });
    } else {
        sizeCheckboxes.forEach(cb => {
            cb.disabled = false;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const allSizeCheckbox = document.querySelector('input[value="All Size"]');
    const sizeCheckboxes = document.querySelectorAll('.size-checkbox');
    
    sizeCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const anyChecked = Array.from(sizeCheckboxes).some(checkbox => checkbox.checked);
            if (anyChecked && allSizeCheckbox) {
                allSizeCheckbox.checked = false;
            }
        });
    });
});
</script>
@endpush
@endsection
