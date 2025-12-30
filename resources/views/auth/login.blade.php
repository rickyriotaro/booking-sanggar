@extends('layouts.auth')

@section('title', 'Login Admin')

@section('content')
<div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-2xl">
    <div class="text-center">
        <img src="{{ asset('images/logo_rants.png') }}" alt="RANTS Logo" class="w-32 h-auto mx-auto mb-4">
        <p class="text-sm text-gray-600 mb-8">
            Admin Panel - RANTS Dashboard
        </p>
    </div>
    
    <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
        @csrf
        
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    autocomplete="email" 
                    required 
                    value="{{ old('email') }}"
                    class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="admin@rants.com"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    autocomplete="current-password" 
                    required 
                    class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="••••••••"
                >
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input 
                    id="remember" 
                    name="remember" 
                    type="checkbox" 
                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                >
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                    Ingat saya
                </label>
            </div>
        </div>

        <div>
            <button 
                type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
            >
                Login
            </button>
        </div>
    </form>

    <div class="text-center text-sm text-gray-500">
        <p>&copy; 2025 RANTS - Ray Entertainments. All rights reserved.</p>
    </div>
</div>
@endsection