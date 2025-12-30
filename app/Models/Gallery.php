<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'gallery';

    protected $fillable = [
        'title',
        'category',
        'image_path',
        'uploaded_by'
    ];

    // Include accessors when converting to JSON
    protected $appends = ['image_url'];

    /**
     * Get the admin who uploaded
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full image URL from storage path
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            // Assuming images stored in public/storage/gallery/
            // and storage:link command has been run
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
