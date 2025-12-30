<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all orders for this user
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all reviews by this user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all addresses for this user
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get primary address
     */
    public function primaryAddress()
    {
        return $this->hasOne(Address::class)->where('is_primary', true);
    }

    /**
     * Get all gallery images uploaded by this user (admin)
     */
    public function galleryImages()
    {
        return $this->hasMany(Gallery::class, 'uploaded_by');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Get all notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
