<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'winner_ticket_id',      // ✅ KORRIGIERT: Das ist das richtige Feld!
        'winner_notified_at',
        'prize_claimed',
        'final_decision',
        'payout_amount',
        'random_seed',
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
    ];

    /**
     * Das Produkt, das verlost wird
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Alle verkauften Tickets für diese Verlosung
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Das gewinnende Ticket
     * ✅ KORRIGIERT: Verwendet jetzt winner_ticket_id (nicht winning_ticket_id)
     */
    public function winningTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'winner_ticket_id');
    }

     public function winnerTicket()
   {
       return $this->belongsTo(\App\Models\Ticket::class, 'winner_ticket_id');
   }

    /**
     * Scope: Nur aktive Verlosungen
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '>', now());
    }

    /**
     * Scope: Verlosungen die bereit für Ziehung sind
     */
    public function scopeReadyForDraw($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '<=', now())
                    ->whereNull('winner_ticket_id');
    }

    /**
     * Scope: Abgeschlossene Verlosungen
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Prüft ob die Verlosung aktiv ist
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    /**
     * Prüft ob die Verlosung abgelaufen ist
     */
    public function isExpired(): bool
    {
        return $this->ends_at <= now();
    }

    /**
     * Prüft ob die Verlosung bereit für Ziehung ist
     */
    public function isReadyForDraw(): bool
    {
        return $this->status === 'active' 
            && $this->ends_at <= now() 
            && !$this->winner_ticket_id;
    }

    /**
     * Berechnet den Fortschritt (0-100%)
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_target <= 0) {
            return 0;
        }

        return min(100, round(($this->total_revenue / $this->total_target) * 100, 2));
    }

    /**
     * Gibt den verbleibenden Betrag bis zum Ziel zurück
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->total_target - $this->total_revenue);
    }

    /**
     * Gibt die Anzahl verbleibender Lose zurück
     */
    public function getRemainingTickets(): int
    {
        return max(0, intval($this->total_target) - $this->tickets_sold);
    }

    /**
     * Prüft ob das Ziel erreicht wurde
     */
    public function targetReached(): bool
    {
        return $this->total_revenue >= $this->total_target;
    }

    /**
     * Gibt einen formatierten Status-Text zurück
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            'scheduled' => 'Geplant',
            'pending' => 'Ausstehend',
            'active' => 'Aktiv',
            'pending_draw' => 'Bereit zur Ziehung',
            'completed' => 'Abgeschlossen',
            'cancelled' => 'Abgebrochen',
            'refunded' => 'Erstattet',
            default => 'Unbekannt'
        };
    }

    /**
     * Gibt eine Status-Badge-Farbe zurück (für Tailwind)
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'gray',
            'pending' => 'gray',
            'active' => 'green',
            'pending_draw' => 'yellow',
            'completed' => 'blue',
            'cancelled' => 'red',
            'refunded' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Gibt verbleibende Zeit als lesbaren String zurück
     */
    public function getRemainingTimeText(): string
    {
        if ($this->isExpired()) {
            return 'Abgelaufen';
        }

        $diff = now()->diff($this->ends_at);
        
        if ($diff->days > 0) {
            return $diff->days . ' Tag' . ($diff->days > 1 ? 'e' : '');
        }
        
        if ($diff->h > 0) {
            return $diff->h . ' Stunde' . ($diff->h > 1 ? 'n' : '');
        }
        
        return $diff->i . ' Minute' . ($diff->i > 1 ? 'n' : '');
    }
}