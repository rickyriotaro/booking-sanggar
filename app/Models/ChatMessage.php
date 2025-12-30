<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'sender_type',
        'sender_id',
        'message',
        'image_name',
        'image_path',
        'image_size',
        'metadata',
        'is_read',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the session this message belongs to
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * Get the chat session
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * Get the sender (user or admin)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if message is from user
     */
    public function isFromUser(): bool
    {
        return $this->sender_type === 'user';
    }

    /**
     * Check if message is from AI
     */
    public function isFromAI(): bool
    {
        return $this->sender_type === 'ai';
    }

    /**
     * Check if message is from admin
     */
    public function isFromAdmin(): bool
    {
        return $this->sender_type === 'admin';
    }

    /**
     * Get image URL accessor
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Check if message has image
     */
    public function hasImage(): bool
    {
        return !is_null($this->image_path);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
