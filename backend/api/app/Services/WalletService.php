<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add balance to user wallet
     */
    public function addBalance($userId, $amount, $referenceId = null, $description = 'Einzahlung')
    {
        $user = User::findOrFail($userId);
        
        DB::beginTransaction();
        try {
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;
            
            // Update user wallet
            $user->wallet_balance = $balanceAfter;
            $user->total_deposited += $amount;
            $user->save();
            
            // Create transaction record
            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceId ? 'raffle' : null,
                'reference_id' => $referenceId,
                'status' => 'completed',
                'description' => $description,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Deduct balance from user wallet
     */
    public function deductBalance($userId, $amount, $raffleId, $description = 'Loskauf')
    {
        $user = User::findOrFail($userId);
        
        if ($user->wallet_balance < $amount) {
            throw new \Exception('Nicht genug Guthaben');
        }
        
        DB::beginTransaction();
        try {
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore - $amount;
            
            // Update user wallet
            $user->wallet_balance = $balanceAfter;
            $user->total_spent += $amount;
            $user->save();
            
            // Create transaction record
            Transaction::create([
                'user_id' => $userId,
                'type' => 'ticket_purchase',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'raffle',
                'reference_id' => $raffleId,
                'status' => 'completed',
                'description' => $description,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process withdrawal
     */
    public function withdraw($userId, $amount, $description = 'Auszahlung')
    {
        $user = User::findOrFail($userId);
        
        if ($user->wallet_balance < $amount) {
            throw new \Exception('Nicht genug Guthaben für Auszahlung');
        }
        
        if ($amount < 10) {
            throw new \Exception('Mindestbetrag für Auszahlung: 10€');
        }
        
        DB::beginTransaction();
        try {
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore - $amount;
            
            // Update user wallet
            $user->wallet_balance = $balanceAfter;
            $user->total_withdrawn += $amount;
            $user->save();
            
            // Create transaction record
            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'pending',
                'description' => $description,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}