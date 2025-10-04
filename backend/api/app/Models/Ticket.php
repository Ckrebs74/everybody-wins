<?php 


// =====================================================
// MODEL 6: app/Models/Ticket.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'raffle_id', 'user_id', 'ticket_number',
        'price', 'status', 'is_bonus_ticket', 'purchased_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_bonus_ticket' => 'boolean',
        'purchased_at' => 'datetime',
    ];

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

    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private static function generateTicketNumber(): string
    {
        do {
            $number = 'TK' . strtoupper(Str::random(8)) . rand(1000, 9999);
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }
}