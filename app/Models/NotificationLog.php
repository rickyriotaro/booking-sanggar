<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'title',
        'message',
        'type',
        'scheduled_at',
        'sent_at',
        'is_sent',
        'read_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    /**
     * Get the user that received this notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order this notification is for
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
