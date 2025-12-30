@extends('layouts.admin')

@section('title', 'Tambah Jasa Tari')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <a href="{{ route('admin.dance-services.index') }}" class="hover:text-red-800">Jasa Tari</a>
        <span>/</span>
        <span>Tambah</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Jasa Tari</h1>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.dance-services.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Paket -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Paket <span class="text-red-600">*</span>
                </label>
                <input type="text" name="package_name" value="{{ old('package_name') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('package_name') border-red-500 @enderror"
                    placeholder="Contoh: Paket Tari Tradisional Premium">
                @error('package_name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipe Tarian -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipe Tarian <span class="text-red-600">*</span>
                </label>
                <select name="dance_type" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('dance_type') border-red-500 @enderror">
                    <option value="">Pilih Tipe Tarian</option>
                    <option value="Tradisional" {{ old('dance_type') == 'Tradisional' ? 'selected' : '' }}>Tradisional</option>
                    <option value="Modern" {{ old('dance_type') == 'Modern' ? 'selected' : '' }}>Modern</option>
                    <option value="Kontemporer" {{ old('dance_type') == 'Kontemporer' ? 'selected' : '' }}>Kontemporer</option>
                    <option value="Kreasi Baru" {{ old('dance_type') == 'Kreasi Baru' ? 'selected' : '' }}>Kreasi Baru</option>
                    <option value="Zapin" {{ old('dance_type') == 'Zapin' ? 'selected' : '' }}>Zapin</option>
                    <option value="Joget" {{ old('dance_type') == 'Joget' ? 'selected' : '' }}>Joget</option>
                    <option value="Tari Melayu" {{ old('dance_type') == 'Tari Melayu' ? 'selected' : '' }}>Tari Melayu</option>
                    <option value="Tari Jawa" {{ old('dance_type') == 'Tari Jawa' ? 'selected' : '' }}>Tari Jawa</option>
                    <option value="Tari Bali" {{ old('dance_type') == 'Tari Bali' ? 'selected' : '' }}>Tari Bali</option>
                    <option value="Tari Minangkabau" {{ old('dance_type') == 'Tari Minangkabau' ? 'selected' : '' }}>Tari Minangkabau</option>
                    <option value="Tari Sunda" {{ old('dance_type') == 'Tari Sunda' ? 'selected' : '' }}>Tari Sunda</option>
                    <option value="Tari Sulawesi" {{ old('dance_type') == 'Tari Sulawesi' ? 'selected' : '' }}>Tari Sulawesi</option>
                    <option value="Tari Dayak" {{ old('dance_type') == 'Tari Dayak' ? 'selected' : '' }}>Tari Dayak</option>
                    <option value="Tari Irian" {{ old('dance_type') == 'Tari Irian' ? 'selected' : '' }}>Tari Irian</option>
                    <option value="Hip Hop" {{ old('dance_type') == 'Hip Hop' ? 'selected' : '' }}>Hip Hop</option>
                    <option value="Jazz" {{ old('dance_type') == 'Jazz' ? 'selected' : '' }}>Jazz</option>
                    <option value="Ballet" {{ old('dance_type') == 'Ballet' ? 'selected' : '' }}>Ballet</option>
                    <option value="Contemporary Dance" {{ old('dance_type') == 'Contemporary Dance' ? 'selected' : '' }}>Contemporary Dance</option>
                    <option value="Belly Dance" {{ old('dance_type') == 'Belly Dance' ? 'selected' : '' }}>Belly Dance</option>
                    <option value="Lainnya" {{ old('dance_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('dance_type')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jumlah Penari -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah Penari <span class="text-red-600">*</span>
                </label>
                <select name="number_of_dancers" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('number_of_dancers') border-red-500 @enderror">
                    <option value="">Pilih Jumlah Penari</option>
                    <option value="3" {{ old('number_of_dancers') == 3 ? 'selected' : '' }}>3 Penari</option>
                    <option value="5" {{ old('number_of_dancers') == 5 ? 'selected' : '' }}>5 Penari</option>
                    <option value="7" {{ old('number_of_dancers') == 7 ? 'selected' : '' }}>7 Penari</option>
                    <option value="9" {{ old('number_of_dancers') == 9 ? 'selected' : '' }}>9 Penari</option>
                    <option value="11" {{ old('number_of_dancers') == 11 ? 'selected' : '' }}>11 Penari</option>
                    <option value="13" {{ old('number_of_dancers') == 13 ? 'selected' : '' }}>13 Penari</option>
                    <option value="15" {{ old('number_of_dancers') == 15 ? 'selected' : '' }}>15 Penari</option>
                </select>
                <p class="text-gray-500 text-xs mt-1">Jumlah penari harus ganjil (3, 5, 7, 9, dst)</p>
                @error('number_of_dancers')
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
                    placeholder="500000" min="0">
                @error('price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Durasi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Durasi (Menit) <span class="text-red-600">*</span>
                </label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('duration_minutes') border-red-500 @enderror"
                    placeholder="60" min="1">
                @error('duration_minutes')
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
                    placeholder="Deskripsi detail tentang paket jasa tari ini...">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Gambar -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Foto Jasa Tari
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
            <a href="{{ route('admin.dance-services.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg">
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
