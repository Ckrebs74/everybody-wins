<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class WalletService
{
    /**
     * Hole aktuelles Guthaben
     */
    public function getBalance(int $userId): float
    {
        $user = User::findOrFail($userId);
        return (float) $user->wallet_balance;
    }

    /**
     * Prüfe ob genug Guthaben vorhanden
     */
    public function hasBalance(int $userId, float $amount): bool
    {
        return $this->getBalance($userId) >= $amount;
    }

    /**
     * Füge Guthaben hinzu (Demo-Mode oder Stripe)
     */
    public function addFunds(int $userId, float $amount, array $metadata = []): bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $balanceBefore = $user->wallet_balance;

            // Erhöhe Balance
            $user->increment('wallet_balance', $amount);
            $user->increment('total_deposited', $amount);

            // Erstelle Transaction
            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $amount,
                'status' => 'completed',
                'description' => 'Guthaben aufgeladen',
                'payment_method' => $metadata['payment_method'] ?? 'demo',
                'external_transaction_id' => $metadata['stripe_payment_intent'] ?? null,
                'metadata' => json_encode($metadata),
            ]);

            DB::commit();

            Log::info('Funds added', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $balanceBefore + $amount,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add funds failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Ziehe Guthaben ab
     */
    public function deductFunds(int $userId, float $amount, array $metadata = []): bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $balanceBefore = $user->wallet_balance;

            // Prüfe Balance
            if ($balanceBefore < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // Verringere Balance
            $user->decrement('wallet_balance', $amount);
            $user->increment('total_spent', $amount);

            // Transaction wird vom TicketController erstellt
            
            DB::commit();

            Log::info('Funds deducted', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $balanceBefore - $amount,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deduct funds failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Stripe Payment Intent erstellen (für echte Zahlungen)
     */
    public function createPaymentIntent(int $userId, float $amount): ?PaymentIntent
    {
        if (config('app.demo_mode', true)) {
            // Demo-Mode: Simuliere erfolgreiche Zahlung
            return null;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Cent
                'currency' => 'eur',
                'metadata' => [
                    'user_id' => $userId,
                    'type' => 'wallet_deposit',
                ],
                'description' => 'Guthaben Aufladung - Jeder Gewinnt!',
            ]);

            return $paymentIntent;

        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Auszahlung durchführen (später mit Stripe)
     */
    public function withdraw(int $userId, float $amount): bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $balanceBefore = $user->wallet_balance;

            // Mindestbetrag 10€
            if ($amount < 10) {
                throw new \Exception('Minimum withdrawal amount is 10€');
            }

            // Prüfe Balance
            if ($balanceBefore < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // Verringere Balance
            $user->decrement('wallet_balance', $amount);
            $user->increment('total_withdrawn', $amount);

            // Erstelle Transaktion
            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $amount,
                'status' => 'pending', // Stripe Payout muss noch erfolgen
                'description' => 'Auszahlung beantragt',
                'metadata' => json_encode([
                    'requested_at' => now()->toISOString(),
                ]),
            ]);

            DB::commit();

            // TODO: Stripe Payout erstellen
            // $this->createStripePayout($userId, $amount);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}