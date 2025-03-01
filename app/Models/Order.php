<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gig_id',
        'buyer_id',
        'seller_id',
        'total_amount',
        'status',
        'delivery_date',
        'requirements',
        'buyer_instructions',
        'seller_notes',
        'revisions_allowed',
        'revisions_used',
        'is_completed',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'revisions_allowed' => 'integer',
        'revisions_used' => 'integer',
    ];
    
    /**
     * Get the gig associated with the order.
     */
    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }
    
    /**
     * Get the buyer associated with the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    /**
     * Get the seller associated with the order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    /**
     * Get the review associated with the order.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
    
    /**
     * Get the messages associated with the order.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    /**
     * Get the transactions associated with the order.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    /**
     * Scope a query to only include orders with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
    
    /**
     * Scope a query to only include active orders.
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }
}
