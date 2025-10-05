<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related raffle (if applicable)
     */
    public function raffle()
    {
        return $this->morphTo('reference');
    }

    /**
     * Get a user-friendly description
     */
    public function getFormattedDescriptionAttribute()
    {
        if ($this->description) {
            return $this->description;
        }

        return match($this->type) {
            'deposit' => 'Einzahlung',
            'withdrawal' => 'Auszahlung',
            'ticket_purchase' => 'Loskauf',
            'winning' => 'Gewinn',
            'refund' => 'Rückerstattung',
            'bonus' => 'Bonus',
            'fee' => 'Gebühr',
            default => $this->type,
        };
    }
}