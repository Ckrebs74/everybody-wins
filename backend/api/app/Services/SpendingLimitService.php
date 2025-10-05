<?php

namespace App\Services;

use App\Models\SpendingLimit;
use Carbon\Carbon;

class SpendingLimitService
{
    private $maxPerHour = 10; // 10€ pro Stunde (Glücksspielregulierung)

    /**
     * Check if user can spend amount
     */
    public function canSpend($userId, $amount)
    {
        $limit = $this->getCurrentHourLimit($userId);
        return ($limit->amount_spent + $amount) <= $this->maxPerHour;
    }

    /**
     * Add spending for user
     */
    public function addSpending($userId, $amount)
    {
        $limit = $this->getCurrentHourLimit($userId);
        $limit->amount_spent += $amount;
        $limit->save();
        
        return $limit;
    }

    /**
     * Get remaining budget for current hour
     */
    public function getRemainingBudget($userId)
    {
        $limit = $this->getCurrentHourLimit($userId);
        return max(0, $this->maxPerHour - $limit->amount_spent);
    }

    /**
     * Get or create spending limit for current hour
     */
    private function getCurrentHourLimit($userId)
    {
        $currentHour = Carbon::now()->startOfHour();
        
        return SpendingLimit::firstOrCreate(
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
     * Get spending history for user
     */
    public function getSpendingHistory($userId, $days = 7)
    {
        return SpendingLimit::where('user_id', $userId)
            ->where('hour_slot', '>=', Carbon::now()->subDays($days))
            ->orderBy('hour_slot', 'desc')
            ->get();
    }

    /**
     * Get spending statistics for user
     */
    public function getStatistics($userId)
    {
        $currentHourSpent = $this->maxPerHour - $this->getRemainingBudget($userId);
        $remaining = $this->getRemainingBudget($userId);

        return [
            'current_hour' => $currentHourSpent,
            'remaining_hour' => $remaining,
            'max_per_hour' => $this->maxPerHour,
            'percentage_used' => ($currentHourSpent / $this->maxPerHour) * 100,
        ];
    }
}