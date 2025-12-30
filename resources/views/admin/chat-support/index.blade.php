@extends('layouts.admin')

@section('title', 'Chat Support')
@section('page-title', 'Chat Support')

@section('content')
<div class="space-y-6">
    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.chat-support.index') }}" 
               class="px-4 py-2 rounded-lg transition font-medium {{ !request('status') ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Semua
            </a>
            <a href="{{ route('admin.chat-support.index', ['status' => 'ai']) }}" 
               class="px-4 py-2 rounded-lg transition font-medium {{ request('status') == 'ai' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                AI Chat
            </a>
            <a href="{{ route('admin.chat-support.index', ['status' => 'human_requested']) }}" 
               class="px-4 py-2 rounded-lg transition font-medium {{ request('status') == 'human_requested' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Butuh Bantuan
                @php
                    $pendingCount = \App\Models\ChatSession::where('status', 'human_requested')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="ml-1 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.chat-support.index', ['status' => 'human_assigned']) }}" 
               class="px-4 py-2 rounded-lg transition font-medium {{ request('status') == 'human_assigned' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Sedang Ditangani
            </a>
            <a href="{{ route('admin.chat-support.index', ['status' => 'closed']) }}" 
               class="px-4 py-2 rounded-lg transition font-medium {{ request('status') == 'closed' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Selesai
            </a>
        </div>
    </div>

    <!-- Chat Sessions List -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pesan Terakhir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="text-red-800 font-medium">{{ substr($session->user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $session->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $session->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-md truncate">
                                @if($session->latestMessage)
                                    {{ Str::limit($session->latestMessage->message, 30) }}
                                @else
                                    <span class="text-gray-400">Belum ada pesan</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($session->status === 'ai')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">AI Chat</span>
                            @elseif($session->status === 'human_requested')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Butuh Bantuan</span>
                            @elseif($session->status === 'human_assigned')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Ditangani</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Selesai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $session->admin ? $session->admin->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $session->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.chat-support.show', $session) }}" 
                               class="text-red-600 hover:text-red-800 font-medium">
                                Lihat Chat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Tidak ada data chat
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
