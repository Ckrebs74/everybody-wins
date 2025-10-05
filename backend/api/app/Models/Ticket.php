<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'raffle_id',
        'user_id',
        'ticket_number',
        'price',
        'status',
        'is_bonus_ticket',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_bonus_ticket' => 'boolean',
        'purchased_at' => 'datetime',
    ];

    // Timestamps
    public $timestamps = false;
    protected $dates = ['purchased_at'];

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
}