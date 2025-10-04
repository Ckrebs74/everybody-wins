<?php

// =====================================================
// MODEL 2: app/Models/Wallet.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'balance', 'bonus_balance', 'locked_balance',
        'total_deposited', 'total_withdrawn'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'bonus_balance' => 'decimal:2',
        'locked_balance' => 'decimal:2',
        'total_deposited' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvailableBalance(): float
    {
        return $this->balance - $this->locked_balance;
    }

    public function canAfford(float $amount): bool
    {
        return $this->getAvailableBalance() >= $amount;
    }
}