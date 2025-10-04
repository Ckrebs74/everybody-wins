<?php

// =====================================================
// MODEL 5: app/Models/Raffle.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'starts_at', 'ends_at', 'drawn_at',
        'target_price', 'platform_fee', 'total_target',
        'status', 'target_reached', 'tickets_sold',
        'total_revenue', 'unique_participants',
        'winner_ticket_id', 'winner_notified_at', 'prize_claimed',
        'final_decision', 'payout_amount', 'random_seed'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'drawn_at' => 'datetime',
        'winner_notified_at' => 'datetime',
        'target_reached' => 'boolean',
        'prize_claimed' => 'boolean',
        'target_price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_target' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($raffle) {
            // Automatisch 30% Platform Fee berechnen
            if (empty($raffle->platform_fee)) {
                $raffle->platform_fee = $raffle->target_price * 0.30;
                $raffle->total_target = $raffle->target_price + $raffle->platform_fee;
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function winnerTicket()
    {
        return $this->belongsTo(Ticket::class, 'winner_ticket_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               now()->between($this->starts_at, $this->ends_at);
    }
}