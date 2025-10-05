<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendingLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hour_slot',
        'amount_spent',
    ];

    protected $casts = [
        'amount_spent' => 'decimal:2',
        'hour_slot' => 'datetime',
    ];

    /**
     * Relationship zum User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}