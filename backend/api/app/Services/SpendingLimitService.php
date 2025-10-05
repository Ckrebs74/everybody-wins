<?php

namespace App\Services;

use App\Models\SpendingLimit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SpendingLimitService
{
    const HOURLY_LIMIT = 10.00; // 10€ pro Stunde (Glücksspielregulierung)

    /**
     * Prüfe ob User noch ausgeben kann
     */
    public function canSpend(int $userId, float $amount): bool
    {
        $currentSpending = $this->getCurrentHourSpending($userId);
        $remaining = self::HOURLY_LIMIT - $currentSpending;

        return $remaining >= $amount;
    }

    /**
     * Hole aktuelle Ausgaben in dieser Stunde
     */
    public function getCurrentHourSpending(int $userId): float
    {
        $hourSlot = $this->getCurrentHourSlot();

        $limit = SpendingLimit::where('user_id', $userId)
            ->where('hour_slot', $hourSlot)
            ->first();

        return $limit ? (float) $limit->amount_spent : 0.00;
    }

    /**
     * Hole verbleibendes Budget
     */
    public function getRemainingBudget(int $userId): float
    {
        $spent = $this->getCurrentHourSpending($userId);
        $remaining = self::HOURLY_LIMIT - $spent;

        return max(0, $remaining);
    }

    /**
     * Erfasse Ausgabe
     */
    public function recordSpending(int $userId, float $amount): bool
    {
        try {
            $hourSlot = $this->getCurrentHourSlot();

            SpendingLimit::updateOrCreate(
                [
                    'user_id' => $userId,
                    'hour_slot' => $hourSlot,
                ],
                [
                    'amount_spent' => DB::raw("amount_spent + {$amount}"),
                ]
            );

            return true;

        } catch (\Exception $e) {
            \Log::error('Record spending failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Hole aktuellen Stunden-Slot (z.B. "2025-10-05 14:00:00")
     */
    private function getCurrentHourSlot(): string
    {
        return Carbon::now()->format('Y-m-d H:00:00');
    }

    /**
     * Hole Spending-Statistiken für User
     */
    public function getStatistics(int $userId): array
    {
        $currentHour = $this->getCurrentHourSpending($userId);
        $remaining = $this->getRemainingBudget($userId);

        // Ausgaben der letzten 24 Stunden
        $last24Hours = SpendingLimit::where('user_id', $userId)
            ->where('hour_slot', '>=', Carbon::now()->subHours(24))
            ->sum('amount_spent');

        // Ausgaben heute
        $today = SpendingLimit::where('user_id', $userId)
            ->whereDate('hour_slot', Carbon::today())
            ->sum('amount_spent');

        return [
            'current_hour' => $currentHour,
            'remaining_hour' => $remaining,
            'last_24_hours' => (float) $last24Hours,
            'today' => (float) $today,
            'limit' => self::HOURLY_LIMIT,
            'percentage_used' => ($currentHour / self::HOURLY_LIMIT) * 100,
        ];
    }

    /**
     * Bereinige alte Einträge (älter als 30 Tage)
     */
    public function cleanup(): int
    {
        return SpendingLimit::where('hour_slot', '<', Carbon::now()->subDays(30))
            ->delete();
    }
}