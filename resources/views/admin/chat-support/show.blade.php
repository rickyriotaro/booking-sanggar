@extends('layouts.admin')

@section('title', 'Chat dengan ' . $chatSession->user->name)
@section('page-title', 'Chat dengan ' . $chatSession->user->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-red-800 to-red-600 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.chat-support.index') }}" class="hover:bg-red-700 p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center">
                        <span class="text-red-800 font-bold text-lg">{{ substr($chatSession->user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">{{ $chatSession->user->name }}</h3>
                        <p class="text-red-100 text-sm">{{ $chatSession->user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($chatSession->status === 'ai')
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">AI Chat</span>
                    @elseif($chatSession->status === 'human_requested')
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Butuh Bantuan</span>
                        <form action="{{ route('admin.chat-support.assign', $chatSession) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white text-red-800 rounded-lg hover:bg-red-50 text-sm font-medium">
                                Ambil Alih Chat
                            </button>
                        </form>
                    @elseif($chatSession->status === 'human_assigned')
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Ditangani: {{ $chatSession->admin->name }}
                        </span>
                        @if($chatSession->admin_id === auth()->id())
                            <button 
                                type="button"
                                onclick="confirmCloseChat({{ $chatSession->id }})"
                                class="px-4 py-2 bg-white text-red-800 rounded-lg hover:bg-red-50 text-sm font-medium">
                                Tutup Chat
                            </button>
                        @endif
                    @else
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Selesai</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="h-[600px] overflow-y-auto p-6 space-y-4 bg-gray-50" id="chatMessages">
            @foreach($chatSession->messages as $message)
                @if($message->sender_type === 'user')
                    <!-- User Message -->
                    <div class="flex justify-end">
                        <div class="flex items-end space-x-2 max-w-xl">
                            <div class="flex-1">
                                <!-- Image if exists -->
                                @if($message->image_url)
                                    <div class="mb-2">
                                        <img src="{{ $message->image_url }}" alt="User image" class="rounded-lg max-h-48 object-cover">
                                        @if($message->image_name)
                                            <p class="text-xs text-gray-500 mt-1">{{ $message->image_name }}</p>
                                        @endif
                                    </div>
                                @endif
                                <!-- Text message -->
                                @if($message->message)
                                    <div class="bg-red-600 text-white rounded-lg rounded-br-none px-4 py-3">
                                        <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                                    </div>
                                @endif
                                <p class="text-xs text-gray-500 mt-1 text-right">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-red-800 text-sm font-medium">{{ substr($chatSession->user->name, 0, 1) }}</span>
                            </div>
                        </div>
                    </div>
                @elseif($message->sender_type === 'ai')
                    <!-- AI Message -->
                    <div class="flex justify-start">
                        <div class="flex items-end space-x-2 max-w-xl">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <!-- Image if exists -->
                                @if($message->image_url)
                                    <div class="mb-2">
                                        <img src="{{ $message->image_url }}" alt="AI analyzed image" class="rounded-lg max-h-48 object-cover">
                                        @if($message->image_name)
                                            <p class="text-xs text-gray-500 mt-1">{{ $message->image_name }}</p>
                                        @endif
                                    </div>
                                @endif
                                <!-- Text message -->
                                <div class="bg-white border border-gray-200 rounded-lg rounded-bl-none px-4 py-3">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-medium text-blue-600">AI Assistant</span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $message->message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($message->sender_type === 'system')
                    <!-- System Message -->
                    <div class="flex justify-center">
                        <p class="text-xs text-gray-500 italic bg-gray-100 px-3 py-1 rounded-full">
                            {{ $message->message }}
                        </p>
                    </div>
                @else
                    <!-- Admin Message -->
                    <div class="flex justify-start">
                        <div class="flex items-end space-x-2 max-w-xl">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-green-800 text-sm font-medium">{{ $message->sender ? substr($message->sender->name, 0, 1) : 'A' }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="bg-green-50 border border-green-200 rounded-lg rounded-bl-none px-4 py-3">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-medium text-green-700">{{ $message->sender ? $message->sender->name : 'Admin' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $message->message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Message Input -->
        @if($chatSession->status !== 'closed')
            <div class="bg-white border-t p-4">
                <form id="sendMessageForm" class="flex space-x-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <textarea 
                            id="messageInput"
                            name="message" 
                            rows="2" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                            placeholder="Ketik balasan Anda..."
                        ></textarea>
                        <div class="mt-2">
                            <label class="flex items-center space-x-2 cursor-pointer text-sm text-gray-600 hover:text-gray-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span>Attach File</span>
                                <input type="file" id="fileInput" name="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt">
                            </label>
                            <div id="fileNameDisplay" class="text-sm text-blue-600 mt-1"></div>
                        </div>
                    </div>
                    <button 
                        type="submit" 
                        class="px-6 h-10 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition flex-shrink-0"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <div class="bg-gray-100 border-t p-4 text-center text-gray-500">
                Chat ini telah ditutup
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const chatSessionId = {{ $chatSession->id }};
    let pollInterval;
    let lastMessageId = {{ $chatSession->messages->count() > 0 ? $chatSession->messages->last()->id : 0 }};

    // Auto scroll to bottom
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // File input handler
    document.getElementById('fileInput')?.addEventListener('change', function(e) {
        const fileName = this.files[0]?.name || '';
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        if (fileName) {
            fileNameDisplay.textContent = '📎 ' + fileName;
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    // Send message via AJAX (no page reload)
    document.getElementById('sendMessageForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('messageInput');
        const fileInput = document.getElementById('fileInput');
        const message = messageInput.value.trim();
        const file = fileInput?.files[0];
        
        if (!message && !file) return;

        try {
            const formData = new FormData();
            formData.append('message', message);
            if (file) {
                formData.append('file', file);
            }
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            const response = await fetch(`/admin/chat-support/${chatSessionId}/message`, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                messageInput.value = '';
                if (fileInput) {
                    fileInput.value = '';
                    document.getElementById('fileNameDisplay').textContent = '';
                }
                // Poll for new messages immediately
                await pollNewMessages();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });

    // Poll for new messages every 1 second
    async function pollNewMessages() {
        try {
            const response = await fetch(`/admin/chat-support/${chatSessionId}/get-messages`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                updateChatMessages(data.messages);
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }

    // Update chat messages
    function updateChatMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        
        let hasNewMessages = false;

        messages.forEach(message => {
            // Only add if message ID is greater than lastMessageId
            if (message.id > lastMessageId) {
                const messageHtml = renderMessage(message);
                chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                lastMessageId = message.id;
                hasNewMessages = true;
            }
        });

        // Only scroll if there are new messages
        if (hasNewMessages) {
            scrollToBottom();
        }
    }

    // Render message HTML
    function renderMessage(message) {
        const time = new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        // Helper function to check if file is image
        const isImage = (fileName) => {
            if (!fileName) return false;
            const imageExts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
            return imageExts.some(ext => fileName.toLowerCase().endsWith(ext));
        };
        
        // Helper function to get file URL
        const getFileUrl = (filePath) => {
            if (!filePath) return '';
            return `/storage/${filePath}`;
        };

        if (message.sender_type === 'system') {
            return `
                <div class="flex justify-center">
                    <p class="text-xs text-gray-500 italic bg-gray-100 px-3 py-1 rounded-full">
                        ${message.message}
                    </p>
                </div>
            `;
        }

        if (message.sender_type === 'user') {
            let fileHtml = '';
            if (message.image_url && message.image_name) {
                if (isImage(message.image_name)) {
                    fileHtml = `
                        <div class="mb-2">
                            <img src="${message.image_url}" alt="User file" class="rounded-lg max-h-48 object-cover">
                            <p class="text-xs text-gray-500 mt-1">${message.image_name}</p>
                        </div>
                    `;
                } else {
                    fileHtml = `
                        <div class="mb-2 bg-gray-100 p-3 rounded-lg flex items-center space-x-2">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">${message.image_name}</p>
                            </div>
                            <a href="${message.image_url}" download class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        </div>
                    `;
                }
            }
            
            return `
                <div class="flex justify-end" data-message-id="${message.id}">
                    <div class="flex items-end space-x-2 max-w-xl">
                        <div class="flex-1">
                            ${fileHtml}
                            ${message.message && !message.message.includes('File:') ? `
                                <div class="bg-red-600 text-white rounded-lg rounded-br-none px-4 py-3">
                                    <p class="text-sm whitespace-pre-wrap">${message.message}</p>
                                </div>
                            ` : ''}
                            <p class="text-xs text-gray-500 mt-1 text-right">${time}</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-red-800 text-sm font-medium">${message.user_name?.charAt(0) || 'U'}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        if (message.sender_type === 'admin') {
            let fileHtml = '';
            if (message.image_url && message.image_name) {
                if (isImage(message.image_name)) {
                    fileHtml = `
                        <div class="mb-2">
                            <img src="${message.image_url}" alt="Admin file" class="rounded-lg max-h-48 object-cover">
                            <p class="text-xs text-gray-500 mt-1">${message.image_name}</p>
                        </div>
                    `;
                } else {
                    fileHtml = `
                        <div class="mb-2 bg-blue-50 p-3 rounded-lg flex items-center space-x-2">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">${message.image_name}</p>
                            </div>
                            <a href="${message.image_url}" download class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        </div>
                    `;
                }
            }
            
            return `
                <div class="flex justify-start" data-message-id="${message.id}">
                    <div class="flex items-end space-x-2 max-w-xl">
                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-green-800 text-sm font-medium">${message.admin_name?.charAt(0) || 'A'}</span>
                        </div>
                        <div class="flex-1">
                            ${fileHtml}
                            <div class="bg-green-50 border border-green-200 rounded-lg rounded-bl-none px-4 py-3">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-medium text-green-700">${message.admin_name || 'Admin'}</span>
                                </div>
                                <p class="text-sm text-gray-800 whitespace-pre-wrap">${message.message.includes('File:') ? '' : message.message}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">${time}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        return '';
    }

    // Start polling
    scrollToBottom();
    pollInterval = setInterval(pollNewMessages, 1000);

    // Cleanup on page unload
    // Close chat with confirmation - using SweetAlert2
    function confirmCloseChat(sessionId) {
        Swal.fire({
            title: 'Tutup Chat?',
            text: 'User akan menerima notifikasi bahwa sesi telah berakhir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Tutup Chat',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                closeChatSession(sessionId);
            }
        });
    }

    function closeChatSession(sessionId) {
        const token = document.querySelector('input[name="_token"]').value;
        
        fetch(`/admin/chat-support/${sessionId}/close`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Chat berhasil ditutup. User telah dikirim notifikasi.',
                    icon: 'success',
                    confirmButtonColor: '#dc2626'
                }).then(() => {
                    window.location.href = '/admin/chat-support';
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Gagal menutup chat',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan saat menutup chat: ' + error.message,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        });
    }

    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
    });
</script>
@endpush
@endsection
