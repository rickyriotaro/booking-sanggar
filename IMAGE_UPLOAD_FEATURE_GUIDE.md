# Chat Feature: Image Upload Implementation Guide

## 📋 Overview

The chat feature now supports image uploads. Users can send images along with text messages to customer service. This document explains the implementation and required database modifications.

---

## 🔧 Technical Architecture

### 1. **Frontend (Flutter) Changes**

#### Updated Components:
- **ChatScreen** (`lib/presentation/screens/home/chat_screen.dart`)
  - Image picker integration
  - Image preview display (60x60 thumbnail in input area)
  - Image display in chat bubbles (200x200 in messages)
  - Clear image button before sending

- **ChatService** (`lib/data/services/chat_service.dart`)
  - `sendMessage()` method updated with image parameters
  - Supports multipart form data for image transmission

- **ChatMessage Model** (`lib/data/models/chat_model.dart`)
  - Added `imageBytes` field (Uint8List)
  - Added `imageName` field (String)
  - Web-compatible image handling

#### Key Features:
- ✅ Web & Mobile compatible (uses Uint8List instead of File objects)
- ✅ Image preview shows before sending
- ✅ Clear button to remove selected image
- ✅ Image cleared automatically after sending
- ✅ Image displays in chat bubble (200x200px)
- ✅ Supports JPEG, PNG, WebP formats
- ✅ Max 1024x1024px, 80% quality (to reduce file size)

---

## 📱 User Flow

```
User opens chat
    ↓
User taps image icon → Image picker opens
    ↓
User selects image → Preview shows (60x60) with filename
    ↓
User types message (optional) + taps send
    ↓
Image sent to backend with message text
    ↓
Image cleared from input field
    ↓
Chat bubble shows message + image (200x200)
```

---

## 💾 Database Schema Migration

### Current Schema (Before)
```sql
CREATE TABLE `chat_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `chat_session_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('user','ai','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Updated Schema (With Image Support)

**Option 1: Add columns to existing table (Recommended)**

```sql
ALTER TABLE `chat_messages` ADD COLUMN `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `message`;
ALTER TABLE `chat_messages` ADD COLUMN `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `image_name`;
ALTER TABLE `chat_messages` ADD COLUMN `image_size` bigint UNSIGNED DEFAULT NULL AFTER `image_path`;
```

**Option 2: Create separate image table (More scalable)**

```sql
CREATE TABLE `chat_message_images` (
  `id` bigint UNSIGNED NOT NULL,
  `chat_message_id` bigint UNSIGNED NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_size` bigint UNSIGNED NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`chat_message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ✅ **RECOMMENDED: Use Option 1 (Simpler)**

Here's the migration you should run:

```sql
-- Add image columns to chat_messages table
ALTER TABLE `chat_messages` 
ADD COLUMN `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `message`,
ADD COLUMN `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `image_name`,
ADD COLUMN `image_size` bigint UNSIGNED DEFAULT NULL AFTER `image_path`;

-- Add index for faster queries
CREATE INDEX `idx_chat_messages_image` ON `chat_messages` (`image_path`);
```

### New Schema (After Migration)
```sql
CREATE TABLE `chat_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `chat_session_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('user','ai','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_size` bigint UNSIGNED DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat_messages_image` (`image_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 Backend API Implementation

### Required Endpoint

The API should handle multipart form data:

```
POST /api/chat/messages
```

#### Request Format (Multipart Form Data)
```
Parameters:
- chat_session_id: int (required)
- message: string (required, can be empty if image present)
- sender_type: string (required, 'user'|'ai'|'admin')
- sender_id: int (optional)
- image: file (optional, max 1MB)
```

#### Example (Laravel)
```php
// routes/api.php
Route::post('/chat/messages', [ChatController::class, 'sendMessage']);

// app/Http/Controllers/ChatController.php
public function sendMessage(Request $request) {
    $validated = $request->validate([
        'chat_session_id' => 'required|integer',
        'message' => 'nullable|string',
        'sender_type' => 'required|in:user,ai,admin',
        'sender_id' => 'nullable|integer',
        'image' => 'nullable|image|max:1024|dimensions:ratio=1/1',
    ]);

    $imagePath = null;
    $imageName = null;
    $imageSize = null;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $imageName = $file->getClientOriginalName();
        $imageSize = $file->getSize();
        
        // Store image (example: storage/app/public/chat-images)
        $imagePath = $file->store('chat-images', 'public');
    }

    $message = ChatMessage::create([
        'chat_session_id' => $validated['chat_session_id'],
        'message' => $validated['message'],
        'sender_type' => $validated['sender_type'],
        'sender_id' => $validated['sender_id'],
        'image_name' => $imageName,
        'image_path' => $imagePath,
        'image_size' => $imageSize,
    ]);

    return response()->json([
        'success' => true,
        'data' => $message,
    ]);
}
```

---

## 📤 Sending Images (Flutter → Backend)

The Flutter app will send images using multipart form data via the `http` package:

```dart
// In ChatService.sendMessage()
if (imageBytes != null) {
    // Using http.MultipartRequest
    final request = http.MultipartRequest('POST', Uri.parse('$_apiBaseUrl/chat/messages'));
    
    request.fields['chat_session_id'] = sessionId.toString();
    request.fields['message'] = message;
    request.fields['sender_type'] = senderType;
    request.sender_id = senderId.toString();
    
    // Add image
    request.files.add(
        http.MultipartFile.fromBytes(
            'image',
            imageBytes,
            filename: imageName,
        ),
    );
    
    final response = await request.send();
}
```

---

## 🎯 Button Behavior Reference

### Header Buttons (ChatScreen)

| Button | Icon | Action | Condition |
|--------|------|--------|-----------|
| **Minimize (-)** | `minimize_rounded` | `Navigator.pop(context)` | Always available |
| **Close (X)** | `close_rounded` | Show dialog → Reset chat & exit | Always available |

### Close Button Dialog

**If messages > 1 (has chat history):**
```
Title: "Tutup Percakapan?"
Message: "Jika Anda meninggalkan percakapan ini, riwayat chat akan dihapus. Anda yakin ingin melanjutkan?"
Buttons:
  - "Batal" → Close dialog
  - "Hapus & Keluar" (red) → Reset + Exit
```

**If messages ≤ 1 (only welcome message):**
```
No dialog → Just pop back to previous screen
```

---

## 🔐 Security Considerations

1. **File Size Validation**
   - Max 1MB per image
   - Validate on both frontend & backend
   - Store outside public_html if possible

2. **File Type Validation**
   - Allow: JPEG, PNG, WebP
   - Reject: executable files, scripts, etc.
   - Validate MIME type on backend

3. **Storage Location**
   ```
   Recommended: storage/app/public/chat-images/
   Access URL: https://yourdomain.com/storage/chat-images/{filename}
   ```

4. **Privacy**
   - Only show images in user's own chat session
   - Validate `chat_session_id` ownership before returning

---

## 📋 Updated Model Classes

### ChatMessage (Data Model)
```dart
class ChatMessage {
  final int id;
  final int chatSessionId;
  final String senderType;
  final int? senderId;
  final String message;
  final Uint8List? imageBytes;     // ← NEW
  final String? imageName;         // ← NEW
  final bool isRead;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  // Constructor, fromJson, toJson...
}
```

### ChatService (Service Layer)
```dart
static Future<ChatMessage> sendMessage({
  required int sessionId,
  required String message,
  required String senderType,
  int? senderId,
  Uint8List? imageBytes,          // ← NEW
  String? imageName,              // ← NEW
}) async {
  // Multipart request with image
}
```

---

## ✅ Testing Checklist

- [ ] Upload small image (< 100KB)
- [ ] Upload large image (> 500KB)
- [ ] Upload without message text
- [ ] Upload with message text
- [ ] Cancel image selection
- [ ] Clear selected image
- [ ] Verify image appears in chat bubble
- [ ] Close chat with dialog confirmation
- [ ] Close chat with only welcome message (no dialog)
- [ ] Test on mobile (Android/iOS)
- [ ] Test on web

---

## 🚀 Next Steps

1. **Run database migration**:
   ```sql
   ALTER TABLE `chat_messages` 
   ADD COLUMN `image_name` varchar(255) DEFAULT NULL AFTER `message`,
   ADD COLUMN `image_path` varchar(255) DEFAULT NULL AFTER `image_name`,
   ADD COLUMN `image_size` bigint UNSIGNED DEFAULT NULL AFTER `image_path`;
   ```

2. **Implement backend endpoint** (ChatController)

3. **Configure file storage** (Laravel storage/public)

4. **Test image upload** flow end-to-end

5. **Deploy changes** to production

---

## 📞 Support

For questions about:
- **Flutter implementation**: Check `lib/presentation/screens/home/chat_screen.dart`
- **Backend integration**: Check Laravel API documentation
- **Database schema**: See migration SQL above

