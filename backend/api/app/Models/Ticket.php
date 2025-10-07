<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'raffle_id',
        'user_id',
        'ticket_number',
        'price',
        'status',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * Die Verlosung, zu der dieses Ticket gehört
     */
    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    /**
     * Der User, der dieses Ticket gekauft hat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Das Produkt über die Raffle-Beziehung
     */
    public function product(): BelongsTo
    {
        return $this->raffle->product();
    }

    /**
     * Scope: Nur aktive Tickets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Gewinnende Tickets
     */
    public function scopeWinning($query)
    {
        return $query->where('status', 'won');
    }

    /**
     * Prüft ob dieses Ticket gewonnen hat
     */
    public function isWinning(): bool
    {
        return $this->status === 'won';
    }

    /**
     * Markiert dieses Ticket als Gewinner
     */
    public function markAsWinner(): void
    {
        $this->update(['status' => 'won']);
    }
}