<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'purchased_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // purchased_at als created_at verwenden (Alias für besseres Verständnis)
    const CREATED_AT = 'purchased_at';
    // updated_at ist jetzt vorhanden
    const UPDATED_AT = 'updated_at';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
            if (empty($ticket->purchased_at)) {
                $ticket->purchased_at = now();
            }
        });
    }

    /**
     * Get the raffle that owns the ticket
     */
    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }

    /**
     * Get the user that owns the ticket
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product through the raffle
     */
    public function product()
    {
        return $this->hasOneThrough(Product::class, Raffle::class, 'id', 'id', 'raffle_id', 'product_id');
    }

    /**
     * Generate unique ticket number
     */
    private static function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . strtoupper(uniqid());
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Scope for valid tickets
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    /**
     * Scope for winner tickets
     */
    public function scopeWinner($query)
    {
        return $query->where('status', 'winner');
    }

    /**
     * Check if ticket is a winner
     */
    public function isWinner(): bool
    {
        return $this->status === 'winner';
    }
}