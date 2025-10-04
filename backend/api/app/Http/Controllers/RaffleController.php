<?php

// =====================================================
// FILE: app/Http/Controllers/RaffleController.php
// =====================================================

namespace App\Http\Controllers;

use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Services\SpendingLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    protected $spendingLimit;

    public function __construct(SpendingLimitService $spendingLimit)
    {
        $this->spendingLimit = $spendingLimit;
    }

    /**
     * Zeige alle aktiven Raffles
     */
    public function index()
    {
        $raffles = Raffle::with(['product', 'product.seller'])
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->paginate(12);

        return view('raffles.index', compact('raffles'));
    }

    /**
     * Zeige einzelnes Raffle
     */
    public function show(Raffle $raffle)
    {
        $raffle->load(['product', 'product.seller', 'tickets']);
        
        // View Count erhöhen
        $raffle->product->increment('view_count');

        $userTickets = null;
        if (Auth::check()) {
            $userTickets = $raffle->tickets()
                ->where('user_id', Auth::id())
                ->count();
        }

        return view('raffles.show', compact('raffle', 'userTickets'));
    }

    /**
     * Ticket kaufen - MIT 10€/STUNDE LIMIT CHECK!
     */
    public function buyTicket(Request $request, Raffle $raffle)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $user = Auth::user();
        $quantity = $request->quantity;
        $totalPrice = $quantity * 1.00; // 1€ pro Ticket

        // KRITISCH: 10€/Stunde Limit prüfen!
        if (!$this->spendingLimit->canSpend($user, $totalPrice)) {
            $remaining = $this->spendingLimit->getRemainingLimit($user);
            return back()->with('error', 
                "Du hast dein Ausgabenlimit erreicht! Du kannst noch maximal {$remaining}€ diese Stunde ausgeben."
            );
        }

        // Prüfe Wallet Balance
        if ($user->wallet->getAvailableBalance() < $totalPrice) {
            return back()->with('error', 'Nicht genügend Guthaben!');
        }

        // Prüfe ob Raffle noch aktiv
        if (!$raffle->isActive()) {
            return back()->with('error', 'Diese Verlosung ist nicht mehr aktiv.');
        }

        DB::beginTransaction();
        try {
            $tickets = [];
            
            // Tickets erstellen
            for ($i = 0; $i < $quantity; $i++) {
                $tickets[] = Ticket::create([
                    'raffle_id' => $raffle->id,
                    'user_id' => $user->id,
                    'ticket_number' => $this->generateTicketNumber(),
                    'price' => 1.00,
                ]);
            }

            // Wallet Update
            $user->wallet->decrement('balance', $totalPrice);

            // Transaction Log
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'ticket_purchase',
                'amount' => $totalPrice,
                'balance_before' => $user->wallet->balance + $totalPrice,
                'balance_after' => $user->wallet->balance,
                'reference_type' => 'raffle',
                'reference_id' => $raffle->id,
                'status' => 'completed',
                'description' => "Kauf von {$quantity} Ticket(s) für {$raffle->product->title}"
            ]);

            // Spending Limit registrieren
            $this->spendingLimit->recordSpending($user, $totalPrice);

            // Raffle Stats Update
            $raffle->increment('tickets_sold', $quantity);
            $raffle->increment('total_revenue', $totalPrice);
            
            // Check if target reached
            if ($raffle->total_revenue >= $raffle->total_target) {
                $raffle->update(['target_reached' => true]);
            }

            DB::commit();

            return back()->with('success', 
                "Du hast {$quantity} Ticket(s) gekauft! Viel Glück bei der Ziehung!"
            );

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Fehler beim Ticketkauf: ' . $e->getMessage());
        }
    }

    private function generateTicketNumber(): string
    {
        do {
            $number = 'TK' . strtoupper(\Str::random(6)) . rand(1000, 9999);
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }
}