<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'admin_id',
        'assigned_at',
        'closed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the chat session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to this session
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get all messages in this session
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the latest message
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    /**
     * Get unread messages count
     */
    public function unreadMessagesCount(): int
    {
        return $this->messages()
            ->where('sender_type', '!=', 'user')
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark session as human requested
     */
    public function requestHuman(): void
    {
        $this->update(['status' => 'human_requested']);
    }

    /**
     * Assign to admin
     */
    public function assignToAdmin(int $adminId): void
    {
        $this->update([
            'status' => 'human_assigned',
            'admin_id' => $adminId,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Close session
     */
    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
