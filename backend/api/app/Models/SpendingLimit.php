<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpendingLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hour_slot',
        'amount_spent'
    ];

    protected $casts = [
        'hour_slot' => 'datetime',
        'amount_spent' => 'decimal:2'
    ];

    /**
     * Get the user that owns the spending limit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create spending limit for current hour
     */
    public static function getCurrentHourLimit($userId)
    {
        $currentHour = Carbon::now()->startOfHour();
        
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'hour_slot' => $currentHour
            ],
            [
                'amount_spent' => 0
            ]
        );
    }

    /**
     * Check if user can spend amount
     */
    public static function canSpend($userId, $amount)
    {
        $limit = self::getCurrentHourLimit($userId);
        $maxPerHour = config('app.max_spending_per_hour', 10);
        
        return ($limit->amount_spent + $amount) <= $maxPerHour;
    }

    /**
     * Add spending for user
     */
    public static function addSpending($userId, $amount)
    {
        $limit = self::getCurrentHourLimit($userId);
        $limit->amount_spent += $amount;
        $limit->save();
        
        return $limit;
    }

    /**
     * Get remaining budget for current hour
     */
    public static function getRemainingBudget($userId)
    {
        $limit = self::getCurrentHourLimit($userId);
        $maxPerHour = config('app.max_spending_per_hour', 10);
        
        return max(0, $maxPerHour - $limit->amount_spent);
    }
}