<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\GeminiChatService;
use Illuminate\Http\Request;

class ChatSupportController extends Controller
{
    private GeminiChatService $chatService;

    public function __construct(GeminiChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Display all chat sessions
     */
    public function index(Request $request)
    {
        $query = ChatSession::with(['user', 'admin', 'latestMessage'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->paginate(10);

        return view('admin.chat-support.index', compact('sessions'));
    }

    /**
     * Show chat conversation
     */
    public function show(ChatSession $chatSession)
    {
        $chatSession->load(['user', 'admin', 'messages.sender']);

        // Mark messages as read
        ChatMessage::where('chat_session_id', $chatSession->id)
            ->where('sender_type', '!=', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.chat-support.show', compact('chatSession'));
    }

    /**
     * Send message as admin (with optional file upload)
     */
    public function sendMessage(Request $request, ChatSession $chatSession)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,txt',
        ]);

        // At least message or file must be provided
        if (empty($request->message) && !$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan atau file harus disediakan',
            ], 422);
        }

        try {
            $filePath = null;
            $fileName = null;
            $fileSize = null;

            // Process file if provided
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                
                // Store file to storage/app/public/chat-files
                $filePath = $file->store('chat-files', 'public');
                
                \Illuminate\Support\Facades\Log::info('File uploaded by admin', [
                    'path' => $filePath,
                    'name' => $fileName,
                    'size' => $fileSize
                ]);
            }

            // Create message with file info
            $message = $request->message ?? '';
            if ($fileName) {
                $message = ($message ? $message . "\n\n" : '') . "📎 File: " . $fileName;
            }

            \App\Models\ChatMessage::create([
                'chat_session_id' => $chatSession->id,
                'sender_type' => 'admin',
                'sender_id' => auth()->id(),
                'message' => $message,
                'image_path' => $filePath,
                'image_name' => $fileName,
                'image_size' => $fileSize,
            ]);

            \Illuminate\Support\Facades\Log::info('Admin message sent', [
                'admin_id' => auth()->id(),
                'session_id' => $chatSession->id,
                'has_file' => $filePath !== null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign session to current admin
     */
    public function assignToMe(ChatSession $chatSession)
    {
        $chatSession->assignToAdmin(auth()->id());

        // REVISI 2: Send automatic welcome message from admin
        $this->chatService->sendAdminWelcomeMessage(
            $chatSession->id,
            auth()->id(),
            auth()->user()->name
        );

        return back()->with('success', 'Chat berhasil diambil alih');
    }

    /**
     * Get new messages via AJAX (Real-time polling)
     */
    public function getMessages(ChatSession $chatSession)
    {
        try {
            $messages = $chatSession->messages()
                ->with('sender')
                ->latest('id')
                ->get()
                ->reverse()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'message' => $message->message,
                        'created_at' => $message->created_at,
                        'user_name' => $message->sender_type === 'user' ? $message->chatSession->user->name : null,
                        'admin_name' => $message->sender_type === 'admin' ? $message->sender->name : null,
                        'image_url' => $message->image_url,
                        'image_name' => $message->image_name,
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close chat session (Admin closes chat)
     */
    public function close(ChatSession $chatSession)
    {
        try {
            // Validate: session must be assigned to current admin
            if ($chatSession->admin_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda bukan admin yang menangani chat ini'
                ], 403);
            }

            // Send system message to user
            ChatMessage::create([
                'chat_session_id' => $chatSession->id,
                'sender_type' => 'system',
                'message' => 'Sesi chat telah berakhir. Silahkan keluar dari pesan dan mulai sesi baru jika perlu bantuan lagi.',
                'is_read' => false
            ]);

            // Close session (keep messages, just change status)
            $chatSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => 'admin',
                'admin_id' => null, // Release admin
            ]);

            \Illuminate\Support\Facades\Log::info('Chat closed by admin', [
                'admin_id' => auth()->id(),
                'session_id' => $chatSession->id
            ]);

            // Check if request expects JSON or redirect
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chat berhasil ditutup'
                ]);
            }

            return redirect()->route('admin.chat-support.index')
                ->with('success', 'Chat berhasil ditutup');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Close chat error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menutup chat: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menutup chat: ' . $e->getMessage());
        }
    }
}
