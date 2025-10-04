<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'user_id',
        'ticket_number',
        'price',
        'status',
        'is_bonus_ticket'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_bonus_ticket' => 'boolean',
        'purchased_at' => 'datetime'
    ];

    const CREATED_AT = 'purchased_at';
    const UPDATED_AT = null;

    // Relationships
    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOneThrough(Product::class, Raffle::class, 'id', 'id', 'raffle_id', 'product_id');
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeWinner($query)
    {
        return $query->where('status', 'winner');
    }
}