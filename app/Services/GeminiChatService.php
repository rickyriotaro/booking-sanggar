<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiChatService
    {
        private string $primaryApiKey;
        private string $fallbackApiKey;
        private string $currentApiKey;
        // Using gemini-2.0-flash which is the latest available model in v1 endpoint
        private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';
        private int $apiKeyIndex = 0; // 0 = primary, 1 = fallback

        public function __construct()
        {
            $this->primaryApiKey = config('services.gemini.api_key');
            $this->fallbackApiKey = config('services.gemini.api_key_fallback') ?? $this->primaryApiKey;
            $this->currentApiKey = $this->primaryApiKey;
        }

    /**
     * Get or create chat session for user
     */
    public function getOrCreateSession(int $userId): ChatSession
    {
        $session = ChatSession::where('user_id', $userId)
            ->where('status', '!=', 'closed')
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'user_id' => $userId,
                'session_id' => Str::uuid(),
                'status' => 'ai',
            ]);

            // Send welcome message
            $this->sendWelcomeMessage($session);
        }

        return $session;
    }

    /**
     * Send welcome message
     */
    private function sendWelcomeMessage(ChatSession $session): void
    {
        $welcomeMessage = "Halo! 👋 Selamat datang di RANTS (Ray Entertainments)!\n\n" .
            "Saya adalah asisten virtual yang siap membantu Anda dengan:\n" .
            "🎭 Informasi Jasa Tari\n" .
            "💄 Informasi Jasa Rias\n" .
            "👘 Sewa Kostum\n" .
            "📦 Status Pesanan\n" .
            "❓ Pertanyaan Umum\n\n" .
            "Silakan tanyakan apa saja! 😊";

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'ai',
            'message' => $welcomeMessage,
        ]);
    }

    /**
     * Send message and get response (UPDATED with image support)
     */
    public function sendMessage(
        int $userId,
        string $message = '',
        ?string $imagePath = null,
        ?string $imageName = null,
        ?int $imageSize = null,
        ?string $imageBase64 = null
    ): array {
        $session = $this->getOrCreateSession($userId);

        // REVISI 1: Jika session sudah assigned ke admin, hanya save message tanpa AI response
        if ($session->status === 'human_assigned') {
            // Save user message
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type' => 'user',
                'sender_id' => $userId,
                'message' => $message,
                'image_path' => $imagePath,
                'image_name' => $imageName,
                'image_size' => $imageSize,
            ]);

            return [
                'success' => true,
                'type' => 'user_message_saved',
                'session_status' => $session->status,
                'message' => 'Pesan Anda telah terkirim ke admin', // Info untuk Flutter
            ];
        }

        // Save user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => $userId,
            'message' => $message,
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_size' => $imageSize,
        ]);

        // REVISI 2: Check if user is requesting human support
        if ($this->isRequestingHumanSupport($message)) {
            $session->requestHuman();

            $responseMessage = "Baik, saya akan menghubungkan Anda dengan admin kami. 👨‍💼\n\n" .
                "Mohon tunggu sebentar ya! Admin kami akan segera membantu Anda! 😊";

            ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type' => 'ai',
                'message' => $responseMessage,
            ]);

            return [
                'message' => $responseMessage,
                'type' => 'ai',
                'session_status' => 'human_requested',
            ];
        }

        // Build context from database
        $context = $this->buildContext($userId, $message);

        // Get chat history for context
        $chatHistory = ChatMessage::where('chat_session_id', $session->id)
            ->orderBy('created_at', 'desc')
            ->limit(10) // Ambil 10 pesan terakhir untuk konteks
            ->get()
            ->reverse(); // Urutkan dari lama ke baru

        // Call Gemini AI
        $aiResponse = $this->callGeminiAPI(
            message: $message,
            context: $context,
            chatHistory: $chatHistory,
            imageBase64: $imageBase64,
            imageName: $imageName
        );

        // Save AI response
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'ai',
            'message' => $aiResponse,
        ]);

        return [
            'message' => $aiResponse,
            'type' => 'ai',
            'session_status' => $session->status,
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
        ];
    }

    /**
     * Build context from database
     */
    private function buildContext(int $userId, string $message): string
    {
        // ✅ TAMBAHKAN INFORMASI WAKTU REALTIME
        $now = now(); // Laravel helper untuk waktu sekarang
        $currentDate = $now->locale('id')->translatedFormat('l, d F Y'); // Senin, 16 Desember 2025
        $currentTime = $now->format('H:i'); // 19:23
        $currentMonth = $now->locale('id')->translatedFormat('F Y'); // Desember 2025
        $currentYear = $now->year; // 2025
        
        $context = "INFORMASI WAKTU SAAT INI:\n";
        $context .= "Tanggal sekarang: {$currentDate}\n";
        $context .= "Waktu sekarang: {$currentTime} WIB\n";
        $context .= "Bulan ini: {$currentMonth}\n";
        $context .= "Tahun ini: {$currentYear}\n\n";
        $context .= "PENTING: Gunakan informasi waktu di atas untuk menjawab pertanyaan tentang jadwal, booking, atau tanggal. JANGAN gunakan tahun 2024 atau tahun lama!\n\n";
        
        $context .= "Anda adalah asisten virtual RANTS (Ray Entertainments), sebuah sanggar tari profesional yang menyediakan berbagai layanan.\n\n";

        $messageLower = strtolower($message);

        // Add costume information if relevant
        if (Str::contains($messageLower, ['kostum', 'baju', 'sewa', 'pakaian'])) {
            $costumes = Costume::where('stock', '>', 0)
                ->select('costume_name as name', 'size', 'rental_price', 'stock')
                ->limit(8)
                ->get();


            if ($costumes->count() > 0) {
                $context .= "KOSTUM YANG TERSEDIA UNTUK DISEWA:\n";
                foreach ($costumes as $costume) {
                    $context .= "• {$costume->name} (Ukuran: {$costume->size})\n";
                    $context .= "  Harga: Rp " . number_format($costume->rental_price, 0, ',', '.') . "/hari\n";
                    $context .= "  Stok tersedia: {$costume->stock} pcs\n";
                }
                $context .= "\n";
            }
        }

        // Add dance service information if relevant
        if (Str::contains($messageLower, ['tari', 'dance', 'penari', 'menari'])) {
            $dances = DanceService::where('is_available', true)
                ->select('package_name', 'dance_type', 'price', 'duration_minutes', 'description')
                ->limit(6)
                ->get();

            if ($dances->count() > 0) {
                $context .= "PAKET JASA TARI YANG TERSEDIA:\n";
                foreach ($dances as $dance) {
                    $context .= "• {$dance->package_name} ({$dance->dance_type})\n";
                    $context .= "  Harga: Rp " . number_format($dance->price, 0, ',', '.') . "\n";
                    $context .= "  Durasi: {$dance->duration_minutes} menit\n";
                    if ($dance->description) {
                        $context .= "  Info: {$dance->description}\n";
                    }
                }
                $context .= "\n";
            }
        }

        // Add makeup service information if relevant
        if (Str::contains($messageLower, ['rias', 'makeup', 'make up', 'dandan'])) {
            $makeups = MakeupService::where('is_available', true)
                ->select('package_name', 'category', 'price', 'description')
                ->limit(6)
                ->get();

            if ($makeups->count() > 0) {
                $context .= "PAKET JASA RIAS YANG TERSEDIA:\n";
                foreach ($makeups as $makeup) {
                    $context .= "• {$makeup->package_name} ({$makeup->category})\n";
                    $context .= "  Harga: Rp " . number_format($makeup->price, 0, ',', '.') . "\n";
                    if ($makeup->description) {
                        $context .= "  Info: {$makeup->description}\n";
                    }
                }
                $context .= "\n";
            }
        }

        // Add order information if relevant
        if (Str::contains($messageLower, ['pesanan', 'order', 'booking', 'status', 'pemesanan', 'jadwal', 'schedule'])) {
            // Jika user bertanya tentang jadwal bulan ini
            if (Str::contains($messageLower, ['bulan ini', 'jadwal bulan', 'booking bulan', 'schedule bulan'])) {
                $now = now();
                $currentMonth = $now->month;
                $currentYear = $now->year;
                
                $monthlyOrders = Order::where('user_id', $userId)
                    ->with('orderDetails')
                    ->whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->orWhere(function($query) use ($userId, $currentYear, $currentMonth) {
                        $query->where('user_id', $userId)
                            ->whereYear('end_date', $currentYear)
                            ->whereMonth('end_date', $currentMonth);
                    })
                    ->orderBy('start_date', 'asc')
                    ->get();
                
                if ($monthlyOrders->count() > 0) {
                    $context .= "JADWAL BOOKING ANDA BULAN INI ({$now->locale('id')->translatedFormat('F Y')}):\n";
                    foreach ($monthlyOrders as $order) {
                        $statusText = match ($order->status) {
                            'pending' => 'Menunggu Konfirmasi',
                            'confirmed' => 'Dikonfirmasi',
                            'in_use' => 'Sedang Digunakan',
                            'returned' => 'Sudah Dikembalikan',
                            'cancelled' => 'Dibatalkan',
                            default => $order->status,
                        };
                        
                        $startDate = \Carbon\Carbon::parse($order->start_date);
                        $endDate = \Carbon\Carbon::parse($order->end_date);
                        
                        $context .= "• Order #{$order->id} - {$statusText}\n";
                        $context .= "  Tanggal: " . $startDate->locale('id')->translatedFormat('d F Y') . " - " . $endDate->locale('id')->translatedFormat('d F Y') . "\n";
                        $context .= "  Total: Rp " . number_format($order->total_price, 0, ',', '.') . "\n";
                    }
                    $context .= "\n";
                } else {
                    $context .= "JADWAL BOOKING ANDA BULAN INI ({$now->locale('id')->translatedFormat('F Y')}): Tidak ada jadwal booking.\n\n";
                }
            } else {
                // Show recent orders (general query)
                $orders = Order::where('user_id', $userId)
                    ->with('orderDetails')
                    ->latest()
                    ->limit(3)
                    ->get();

            if ($orders->count() > 0) {
                $context .= "PESANAN ANDA:\n";
                $now = now();
                foreach ($orders as $order) {
                    $statusText = match ($order->status) {
                        'pending' => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'in_use' => 'Sedang Digunakan',
                        'returned' => 'Sudah Dikembalikan',
                        'cancelled' => 'Dibatalkan',
                        default => $order->status,
                    };
                    
                    // Hitung apakah order ini upcoming, ongoing, atau past
                    $startDate = \Carbon\Carbon::parse($order->start_date);
                    $endDate = \Carbon\Carbon::parse($order->end_date);
                    $timeStatus = '';
                    
                    if ($now->lt($startDate)) {
                        $daysUntil = $now->diffInDays($startDate);
                        $timeStatus = "Akan datang ({$daysUntil} hari lagi)";
                    } elseif ($now->between($startDate, $endDate)) {
                        $timeStatus = "Sedang berlangsung";
                    } else {
                        $timeStatus = "Sudah lewat";
                    }

                    $context .= "• Order #{$order->id}\n";
                    $context .= "  Status: {$statusText}\n";
                    $context .= "  Tanggal: " . $startDate->locale('id')->translatedFormat('d F Y') . " - " . $endDate->locale('id')->translatedFormat('d F Y') . "\n";
                    $context .= "  Waktu: {$timeStatus}\n";
                    $context .= "  Total: Rp " . number_format($order->total_price, 0, ',', '.') . "\n";
                }
                $context .= "\n";
            }
            }
        }

        // Add instructions for AI
        $context .= "INSTRUKSI PENTING:\n";
        $context .= "1. Jawab dengan ramah, profesional, dan helpful\n";
        $context .= "2. Gunakan Bahasa Indonesia yang baik\n";
        $context .= "3. Gunakan emoji yang sesuai untuk membuat percakapan lebih friendly (tapi jangan berlebihan)\n";
        $context .= "4. JANGAN PERNAH mengirim greeting/sambutan seperti 'Halo, Selamat datang' di tengah percakapan yang sedang berjalan\n";
        $context .= "5. Langsung jawab pertanyaan user tanpa pembukaan formal jika percakapan sudah dimulai\n";
        $context .= "6. SELALU gunakan tanggal dan waktu yang SUDAH DIBERIKAN di awal context. JANGAN PERNAH gunakan tahun 2024 atau tanggal lama sebagai placeholder!\n";
        $context .= "7. Jika user bertanya tentang 'bulan ini', 'tahun ini', atau 'sekarang', gunakan informasi waktu yang sudah diberikan\n";
        $context .= "8. Jika pertanyaan di luar konteks layanan RANTS, jawab secara umum tapi tetap dalam koridor sanggar tari\n";
        $context .= "9. Jika user bertanya tentang cara pemesanan, jelaskan mereka bisa memesan langsung di aplikasi\n";
        $context .= "10. Jika user butuh bantuan teknis atau komplain serius, sarankan untuk ketik 'hubungi admin' atau 'butuh bantuan admin'\n";
        $context .= "11. Berikan informasi yang akurat berdasarkan data yang diberikan\n";
        $context .= "12. Jika tidak ada data yang relevan, jelaskan bahwa layanan tersebut mungkin tidak tersedia saat ini\n";
        $context .= "13. Selalu akhiri dengan pertanyaan ramah untuk melanjutkan percakapan\n\n";

        return $context;
    }

    /**
     * Call Gemini AI API (UPDATED with image support, API key fallback, CACHING, and CHAT HISTORY)
     */
    private function callGeminiAPI(
        string $message, 
        string $context, 
        $chatHistory = null, // Tambah parameter chat history
        ?string $imageBase64 = null, 
        ?string $imageName = null
    ): string
    {
        try {
            // ✅ DISABLED CACHE: Karena sekarang response bergantung pada chat history
            // Setiap response harus mempertimbangkan konteks percakapan sebelumnya
            // Jadi tidak bisa di-cache
            
            if (empty($this->primaryApiKey)) {
                Log::error('Gemini API keys are not configured');
                return "Maaf, layanan AI chat sedang tidak tersedia. Silakan ketik 'hubungi admin' untuk bantuan langsung. 😊";
            }

            // ✅ NEW: Build conversation history untuk Gemini
            $contents = [];
            
            // Jika ada chat history, tambahkan ke contents
            if ($chatHistory && $chatHistory->count() > 0) {
                foreach ($chatHistory as $msg) {
                    // Skip welcome message dari AI (yang pertama kali)
                    if ($msg->sender_type === 'ai' && strpos($msg->message, 'Selamat datang di RANTS') !== false) {
                        continue;
                    }
                    
                    $role = $msg->sender_type === 'user' ? 'user' : 'model';
                    $contents[] = [
                        'role' => $role,
                        'parts' => [
                            ['text' => $msg->message]
                        ]
                    ];
                }
            }

            // Build parts array untuk pesan saat ini
            $currentParts = [
                ['text' => $context . "\nPertanyaan customer: " . $message]
            ];

            // Add image if provided
            if ($imageBase64 && $imageName) {
                $mimeType = $this->getMimeType($imageName);
                $currentParts[] = [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => $imageBase64,
                    ]
                ];

                // Add instruction untuk AI mengenai gambar
                $currentParts[] = [
                    'text' => $this->buildImageContext($imageName)
                ];
            }


            // Tambahkan pesan user saat ini ke conversation
            $contents[] = [
                'role' => 'user',
                'parts' => $currentParts
            ];

            // Try primary API key first, fallback to secondary if needed
            $response = $this->makeGeminiRequest($contents);

            // If primary key fails with 429 (rate limit), try fallback
            if (!$response->successful() && $response->status() == 429 && $this->currentApiKey === $this->primaryApiKey) {
                Log::warning('Primary API key rate limited, trying fallback key');
                $this->currentApiKey = $this->fallbackApiKey;
                $response = $this->makeGeminiRequest($contents);
            }


            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    // Reset to primary key if successful
                    $this->currentApiKey = $this->primaryApiKey;
                    
                    $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'];
                    
                    // ✅ CACHING DISABLED: Response sekarang bergantung pada conversation history
                    
                    return $aiResponse;
                }

                Log::warning('Gemini API returned unexpected response structure', ['response' => $data]);
                return "Maaf, saya tidak dapat memproses permintaan Anda saat ini. Silakan coba lagi atau ketik 'hubungi admin'. 😊";
            }

            // Log error details
            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $this->apiUrl,
                'api_key_used' => $this->currentApiKey === $this->primaryApiKey ? 'primary' : 'fallback',
                'request_payload' => [
                    'contents_count' => count($parts ?? []),
                    'image_included' => $imageBase64 ? 'yes' : 'no'
                ]
            ]);

            // More detailed error message based on status code
            $statusCode = $response->status();
            if ($statusCode == 400) {
                return "Maaf, ada masalah dengan format permintaan. Coba lagi atau hubungi admin. 😊";
            } elseif ($statusCode == 401) {
                return "Maaf, ada masalah dengan autentikasi AI. Hubungi admin untuk bantuan. 😊";
            } elseif ($statusCode == 429) {
                Log::warning('⚠️ Rate limited on all API keys');
                return "Maaf, server AI sedang overloaded. Silakan coba lagi dalam 30 detik atau hubungi admin untuk bantuan langsung. 😊";
            }

            return "Maaf, sistem sedang mengalami gangguan teknis. Tim kami akan segera memperbaikinya. Coba lagi dalam beberapa saat atau ketik 'hubungi admin' untuk bantuan. 😊";
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return "Maaf, sistem sedang mengalami gangguan. Silakan ketik 'hubungi admin' untuk bantuan langsung dari tim kami. 😊";
        }
    }

    /**
     * Make HTTP request to Gemini API with current API key
     */
    private function makeGeminiRequest(array $contents): \Illuminate\Http\Client\Response
    {
        Log::info('Gemini API Request', [
            'url' => $this->apiUrl,
            'api_key_used' => $this->currentApiKey === $this->primaryApiKey ? 'primary' : 'fallback',
            'key_prefix' => substr($this->currentApiKey, 0, 5) . '...',
            'conversation_turns' => count($contents)
        ]);

        return Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiUrl . '?key=' . $this->currentApiKey, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                    'topP' => 0.9,
                    'topK' => 20,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ]
                ]
            ]);
    }

    /**
     * Get MIME type from filename (NEW)
     */
    private function getMimeType(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    /**
     * Build context from image (NEW)
     */
    private function buildImageContext(?string $imageName): string
    {
        if (!$imageName) {
            return '';
        }

        return "\n\nPERHATIAN: User mengirim gambar dengan nama: {$imageName}. " .
            "Jika gambar relevan dengan pertanyaan, gunakan konteks gambar untuk membantu menjawab. " .
            "Berikan komentar tentang gambar jika relevan dengan layanan kami (kostum, tari, rias).\n";
    }

    /**
     * Check if user is requesting human support
     */
    private function isRequestingHumanSupport(string $message): bool
    {
        $lowerMessage = strtolower($message);

        // Only trigger human support for specific phrases/keywords
        $supportKeywords = [
            'hubungi admin',
            'minta bantuan',
            'komplain',
            'keluhan',
            'tidak puas',
            'kecewa',
            'bicara dengan admin',
            'bicara dengan operator',
            'customer service',
            'cs saja',
            'bantuan admin',
            'operator',
            'butuh bantuan real',
            'berbicara dengan manusia',
        ];

        foreach ($supportKeywords as $keyword) {
            if (Str::contains($lowerMessage, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get chat history (UPDATED)
     */
    public function getChatHistory(int $userId, int $limit = 100): array
    {
        $session = $this->getOrCreateSession($userId);

        $messages = ChatMessage::where('chat_session_id', $session->id)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_type' => $msg->sender_type,
                    'created_at' => $msg->created_at->format('Y-m-d H:i:s'),
                    'is_read' => $msg->is_read,
                    'image_url' => $msg->image_path ? asset('storage/' . $msg->image_path) : null,
                    'image_name' => $msg->image_name,
                    'image_size' => $msg->image_size,
                ];
            });

        return [
            'session_id' => $session->session_id,
            'status' => $session->status,
            'messages' => $messages,
        ];
    }

    /**
     * Admin send message to customer
     */
    public function sendAdminMessage(int $sessionId, int $adminId, string $message): ChatMessage
    {
        $session = ChatSession::findOrFail($sessionId);

        // Assign admin if not assigned yet
        if ($session->status === 'human_requested') {
            $session->assignToAdmin($adminId);
        }

        return ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'message' => $message,
        ]);
    }

    /**
     * REVISI 2: Send automatic welcome message from admin when assigned
     */
    public function sendAdminWelcomeMessage(int $sessionId, int $adminId, string $adminName): void
    {
        $welcomeMessage = "Halo! 👋 Saya {$adminName} dari RANTS.\n\n" .
            "Ada yang bisa saya bantu? Silakan sampaikan keluhan atau pertanyaan Anda. 😊";

        ChatMessage::create([
            'chat_session_id' => $sessionId,
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'message' => $welcomeMessage,
        ]);
    }

    /**
     * REVISI 3: Delete user chat session and all messages
     */
    public function deleteUserChat(int $userId): bool
    {
        $session = ChatSession::where('user_id', $userId)
            ->where('status', '!=', 'closed')
            ->first();

        if (!$session) {
            return false;
        }

        // Delete all messages in this session
        ChatMessage::where('chat_session_id', $session->id)->delete();

        // Delete the session
        $session->delete();

        Log::info('Chat session deleted', [
            'user_id' => $userId,
            'session_id' => $session->session_id,
        ]);

        return true;
    }
}
