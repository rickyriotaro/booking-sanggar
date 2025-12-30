@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="max-w-4xl">
    <!-- Profile Information Card -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Informasi Profil</h3>
            <p class="text-sm text-gray-600 mt-1">Perbarui informasi profil dan alamat email Anda</p>
        </div>
        
        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $user->name) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon
                    </label>
                    <input 
                        type="text" 
                        id="phone_number" 
                        name="phone_number" 
                        value="{{ old('phone_number', $user->phone_number) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('phone_number') border-red-500 @enderror"
                        placeholder="08xxxxxxxxxx"
                    >
                    @error('phone_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role (Read Only) -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role
                    </label>
                    <input 
                        type="text" 
                        id="role" 
                        value="{{ ucfirst($user->role) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 cursor-not-allowed"
                        readonly
                    >
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
            <p class="text-sm text-gray-600 mt-1">Pastikan akun Anda menggunakan password yang kuat untuk keamanan</p>
        </div>
        
        <form action="{{ route('admin.profile.password') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('current_password') border-red-500 @enderror"
                        required
                    >
                    @error('current_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('new_password') border-red-500 @enderror"
                        required
                    >
                    @error('new_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="new_password_confirmation" 
                        name="new_password_confirmation" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                        required
                    >
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition"
                    >
                        Ubah Password
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
