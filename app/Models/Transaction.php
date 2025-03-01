<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'order_id',
        'buyer_id',
        'seller_id',
        'amount',
        'platform_fee',
        'seller_amount',
        'currency',
        'payment_method',
        'payment_status',
        'transaction_type',
        'is_escrow',
        'escrow_released_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'is_escrow' => 'boolean',
        'escrow_released_at' => 'datetime',
    ];

    /**
     * Get the order associated with the transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the buyer associated with the transaction.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller associated with the transaction.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Scope a query to only include transactions with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to only include transactions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope a query to only include escrow transactions.
     */
    public function scopeEscrow($query)
    {
        return $query->where('is_escrow', true);
    }

    /**
     * Scope a query to only include released escrow transactions.
     */
    public function scopeReleased($query)
    {
        return $query->whereNotNull('escrow_released_at');
    }
}
