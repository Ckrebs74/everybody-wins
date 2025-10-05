<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    protected $fillable = [
        'product_id',
        'starts_at',
        'ends_at',
        'drawn_at',
        'target_price',
        'platform_fee',
        'total_target',
        'status',
        'target_reached',
        'tickets_sold',
        'total_revenue',
        'unique_participants',
        'winner_ticket_id',
        'winner_notified_at',
        'prize_claimed',
        'final_decision',
        'payout_amount',
        'random_seed',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_target' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'target_reached' => 'boolean',
        'prize_claimed' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'drawn_at' => 'datetime',
        'winner_notified_at' => 'datetime',
    ];

    /**
     * Get the product that owns the raffle
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the tickets for the raffle
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the winning ticket
     */
    public function winnerTicket()
    {
        return $this->belongsTo(Ticket::class, 'winner_ticket_id');
    }
}