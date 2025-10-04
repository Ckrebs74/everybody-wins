<?php

// =====================================================
// MODEL 3: app/Models/SpendingLimit.php
// KRITISCH für 10€/Stunde Regulierung!
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendingLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'hour_slot', 'amount_spent'
    ];

    protected $casts = [
        'hour_slot' => 'datetime',
        'amount_spent' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}