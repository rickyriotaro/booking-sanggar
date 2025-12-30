<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone_number',
        'full_address',
        'province',
        'city',
        'district',
        'postal_code',
        'notes',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Relationship: Address belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set sebagai alamat utama
     */
    public function setPrimary()
    {
        // Set semua alamat user menjadi non-primary
        self::where('user_id', $this->user_id)->update(['is_primary' => false]);
        
        // Set alamat ini sebagai primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Format alamat lengkap
     */
    public function getFormattedAddressAttribute()
    {
        $parts = [
            $this->full_address,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ];

        return implode(', ', array_filter($parts));
    }
}
