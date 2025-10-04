<?php

// =====================================================
// SERVICE: app/Services/SpendingLimitService.php
// KERN-FEATURE: 10€/Stunde Limit Enforcement
// =====================================================

namespace App\Services;

use App\Models\SpendingLimit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpendingLimitService
{
    const MAX_SPENDING_PER_HOUR = 10.00; // 10€ pro Stunde

    /**
     * Prüft ob der User noch Geld ausgeben darf
     */
    public function canSpend(User $user, float $amount): bool
    {
        $currentHourSpending = $this->getCurrentHourSpending($user);
        return ($currentHourSpending + $amount) <= self::MAX_SPENDING_PER_HOUR;
    }

    /**
     * Gibt zurück wieviel der User diese Stunde schon ausgegeben hat
     */
    public function getCurrentHourSpending(User $user): float
    {
        $hourSlot = $this->getCurrentHourSlot();
        
        $spending = SpendingLimit::where('user_id', $user->id)
            ->where('hour_slot', $hourSlot)
            ->first();

        return $spending ? $spending->amount_spent : 0.00;
    }

    /**
     * Gibt zurück wieviel der User noch ausgeben kann
     */
    public function getRemainingLimit(User $user): float
    {
        $spent = $this->getCurrentHourSpending($user);
        return max(0, self::MAX_SPENDING_PER_HOUR - $spent);
    }

    /**
     * Registriert eine Ausgabe
     */
    public function recordSpending(User $user, float $amount): bool
    {
        if (!$this->canSpend($user, $amount)) {
            throw new \Exception('Ausgabenlimit überschritten! Max 10€ pro Stunde.');
        }

        $hourSlot = $this->getCurrentHourSlot();

        return DB::transaction(function () use ($user, $amount, $hourSlot) {
            $spending = SpendingLimit::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'hour_slot' => $hourSlot
                ],
                ['amount_spent' => 0]
            );

            $spending->increment('amount_spent', $amount);

            return true;
        });
    }

    /**
     * Gibt den aktuellen Stunden-Slot zurück
     */
    private function getCurrentHourSlot(): string
    {
        return Carbon::now()->format('Y-m-d H:00:00');
    }

    /**
     * Reset für Tests
     */
    public function resetUserLimit(User $user): void
    {
        SpendingLimit::where('user_id', $user->id)
            ->where('hour_slot', $this->getCurrentHourSlot())
            ->delete();
    }
}