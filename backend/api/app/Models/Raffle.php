<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raffle extends Model
{
    use HasFactory;

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
        'random_seed'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'drawn_at' => 'datetime',
        'winner_notified_at' => 'datetime',
        'target_price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_target' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'target_reached' => 'boolean',
        'prize_claimed' => 'boolean',
        'tickets_sold' => 'integer',
        'unique_participants' => 'integer'
    ];

    // Relationships
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function hasEnded()
    {
        return $this->ends_at <= now();
    }

    public function getProgressPercentage()
    {
        if ($this->total_target <= 0) return 0;
        return min(100, round(($this->total_revenue / $this->total_target) * 100, 2));
    }
}