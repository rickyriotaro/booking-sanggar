<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GeminiChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private GeminiChatService $chatService;

    public function __construct(GeminiChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get chat history
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->chatService->getChatHistory($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Chat history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat chat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send message with optional image (UPDATED)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,webp',
        ]);

        // At least message or image must be provided
        if (empty($validated['message']) && !$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan atau gambar harus disediakan',
            ], 422);
        }

        try {
            $imagePath = null;
            $imageName = null;
            $imageSize = null;
            $imageBase64 = null;

            // Process image if provided
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $imageName = $file->getClientOriginalName();
                $imageSize = $file->getSize();
                
                // Store image to storage/app/public/chat-images
                $storagePath = $file->store('chat-images', 'public');
                $imagePath = $storagePath;
                
                Log::info('Image stored', ['path' => $imagePath, 'size' => $imageSize]);
                
                // Convert to base64 untuk dikirim ke Gemini
                $imageBase64 = base64_encode(file_get_contents($file->getRealPath()));
            }

            Log::info('Sending chat message', [
                'user_id' => $request->user()->id,
                'has_message' => !empty($validated['message']),
                'has_image' => $imagePath !== null
            ]);

            // Send message to AI
            $response = $this->chatService->sendMessage(
                userId: $request->user()->id,
                message: $validated['message'] ?? '',
                imagePath: $imagePath,
                imageName: $imageName,
                imageSize: $imageSize,
                imageBase64: $imageBase64
            );

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request human support
     */
    public function requestHumanSupport(Request $request): JsonResponse
    {
        try {
            $session = $this->chatService->getOrCreateSession($request->user()->id);
            $session->requestHuman();

            Log::info('Human support requested', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Permintaan bantuan admin telah dikirim. Admin kami akan segera membantu Anda.',
            ]);
        } catch (\Exception $e) {
            Log::error('Human support error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim permintaan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * REVISI 3: Delete chat session and all messages
     */
    public function deleteChat(Request $request): JsonResponse
    {
        try {
            $deleted = $this->chatService->deleteUserChat($request->user()->id);

            if ($deleted) {
                Log::info('Chat deleted', ['user_id' => $request->user()->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Chat berhasil dihapus',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada chat yang dihapus',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Delete chat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus chat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Keep session alive - User exits but keeps chat active for admin reply
     */
    public function keepAlive(Request $request): JsonResponse
    {
        try {
            $session = $this->chatService->getOrCreateSession($request->user()->id);
            
            if ($session->status !== 'human_assigned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak dalam status ditangani'
                ], 400);
            }

            // Update last activity timestamp
            $session->update([
                'last_activity_at' => now(),
            ]);

            Log::info('Session keep alive', [
                'user_id' => $request->user()->id,
                'session_id' => $session->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session disimpan',
                'session_id' => $session->id,
                'status' => $session->status,
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Keep alive error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Close session - User explicitly closes chat with admin
     */
    public function closeSession(Request $request): JsonResponse
    {
        try {
            $session = $this->chatService->getOrCreateSession($request->user()->id);
            
            if ($session->status !== 'human_assigned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak dalam status ditangani'
                ], 400);
            }

            // Send system message ke admin
            \App\Models\ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type' => 'system',
                'message' => 'Customer telah menutup chat',
                'is_read' => false
            ]);

            // Close session
            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => 'customer',
                'admin_id' => null, // Release admin
            ]);

            Log::info('Session closed by customer', [
                'user_id' => $request->user()->id,
                'session_id' => $session->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat berhasil ditutup',
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Close session error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
