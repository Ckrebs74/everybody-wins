<?php

// =====================================================
// FILE: app/Http/Controllers/DashboardController.php
// =====================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SpendingLimitService;

class DashboardController extends Controller
{
    protected $spendingLimit;

    public function __construct(SpendingLimitService $spendingLimit)
    {
        $this->spendingLimit = $spendingLimit;
    }

    /**
     * User Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $user->load(['wallet', 'tickets.raffle.product']);

        // Spending Limit Info
        $currentSpending = $this->spendingLimit->getCurrentHourSpending($user);
        $remainingLimit = $this->spendingLimit->getRemainingLimit($user);

        // Aktive Tickets
        $activeTickets = $user->tickets()
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->count();

        // Gewonnene Raffles
        $wonRaffles = $user->tickets()
            ->where('status', 'winner')
            ->count();

        return view('dashboard', compact(
            'user', 
            'currentSpending', 
            'remainingLimit',
            'activeTickets',
            'wonRaffles'
        ));
    }

    /**
     * Wallet aufladen
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:100'
        ]);

        // Hier würde normalerweise Payment Provider kommen
        // Für Test einfach Balance erhöhen
        
        $user = Auth::user();
        $amount = $request->amount;

        DB::beginTransaction();
        try {
            $user->wallet->increment('balance', $amount);
            $user->wallet->increment('total_deposited', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $user->wallet->balance - $amount,
                'balance_after' => $user->wallet->balance,
                'status' => 'completed',
                'description' => "Einzahlung von {$amount}€"
            ]);

            DB::commit();

            return back()->with('success', "{$amount}€ erfolgreich eingezahlt!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Einzahlung fehlgeschlagen.');
        }
    }
}