<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'title',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'social_links',
        'skills',
        'languages',
        'account_type',
        'is_verified_seller',
        'last_active_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'social_links' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'is_verified_seller' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
