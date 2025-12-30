<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'data', 'type', 'is_read', 'read_at'];
    protected $casts = ['data' => 'array', 'is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
