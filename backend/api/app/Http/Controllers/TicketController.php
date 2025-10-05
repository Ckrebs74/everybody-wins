<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Raffle;
use App\Models\Transaction;
use App\Services\WalletService;
use App\Services\SpendingLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class TicketController extends Controller
{
    protected $walletService;
    protected $spendingLimitService;

    public function __construct(WalletService $walletService, SpendingLimitService $spendingLimitService)
    {
        $this->middleware('auth');
        $this->walletService = $walletService;
        $this->spendingLimitService = $spendingLimitService;
    }

    /**
     * Ticket-Kauf durchführen
     */
    public function purchase(Request $request, $raffleId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $user = Auth::user();
        $quantity = $request->quantity;
        $totalAmount = $quantity * 1.00; // 1€ pro Los

        // Lade Raffle mit Product
        $raffle = Raffle::with('product')->findOrFail($raffleId);

        // Validierungen
        if ($raffle->status !== 'active') {
            return back()->with('error', 'Diese Verlosung ist nicht mehr aktiv.');
        }

        if ($raffle->ends_at <= now()) {
            return back()->with('error', 'Diese Verlosung ist bereits beendet.');
        }

        // Prüfe ob Seller nicht selbst kaufen kann
        if ($raffle->product->seller_id === $user->id) {
            return back()->with('error', 'Sie können nicht an Ihrer eigenen Verlosung teilnehmen.');
        }

        // Prüfe Spending Limit (10€/Stunde)
        if (!$this->spendingLimitService->canSpend($user->id, $totalAmount)) {
            $remaining = $this->spendingLimitService->getRemainingBudget($user->id);
            return back()->with('error', "Ausgabenlimit erreicht! Sie können in dieser Stunde noch {$remaining}€ ausgeben.");
        }

        // Prüfe Wallet-Balance
        if (!$this->walletService->hasBalance($user->id, $totalAmount)) {
            $balance = $this->walletService->getBalance($user->id);
            return back()->with('error', "Nicht genug Guthaben! Aktuelles Guthaben: {$balance}€. Benötigt: {$totalAmount}€");
        }

        DB::beginTransaction();
        try {
            // Erstelle Tickets
            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $ticket = Ticket::create([
                    'raffle_id' => $raffle->id,
                    'user_id' => $user->id,
                    'ticket_number' => $this->generateTicketNumber(),
                    'price' => 1.00,
                    'status' => 'valid',
                    'is_bonus_ticket' => false,
                    'purchased_at' => now(),
                ]);
                $tickets[] = $ticket;
            }

            // Wallet abziehen
            $this->walletService->deductFunds($user->id, $totalAmount, [
                'type' => 'ticket_purchase',
                'raffle_id' => $raffle->id,
                'quantity' => $quantity,
            ]);

            // Spending Limit aktualisieren
            $this->spendingLimitService->recordSpending($user->id, $totalAmount);

            // Raffle-Statistiken aktualisieren
            $raffle->increment('tickets_sold', $quantity);
            $raffle->increment('total_revenue', $totalAmount);

            // Unique Participants erhöhen wenn erster Kauf
            $existingTickets = Ticket::where('raffle_id', $raffle->id)
                ->where('user_id', $user->id)
                ->count();
            
            if ($existingTickets === $quantity) {
                $raffle->increment('unique_participants');
            }

            // Transaction erstellen
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'ticket_purchase',
                'amount' => -$totalAmount,
                'balance_before' => $this->walletService->getBalance($user->id) + $totalAmount,
                'balance_after' => $this->walletService->getBalance($user->id),
                'reference_type' => 'raffle',
                'reference_id' => $raffle->id,
                'status' => 'completed',
                'description' => "{$quantity} Los(e) für {$raffle->product->title}",
                'metadata' => json_encode([
                    'ticket_numbers' => array_map(fn($t) => $t->ticket_number, $tickets),
                ]),
            ]);

            DB::commit();

            return redirect()
                ->route('raffles.show', $raffle->id)
                ->with('success', "🎉 Erfolgreich {$quantity} Los(e) gekauft! Viel Glück bei der Verlosung!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket purchase failed', [
                'user_id' => $user->id,
                'raffle_id' => $raffleId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Fehler beim Ticketkauf. Bitte versuchen Sie es erneut.');
        }
    }

    /**
     * Generiere eindeutige Ticket-Nummer
     */
    private function generateTicketNumber(): string
    {
        do {
            $number = 'TK-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Zeige User's Tickets
     */
    public function myTickets()
    {
        $user = Auth::user();
        
        $tickets = Ticket::with(['raffle.product'])
            ->where('user_id', $user->id)
            ->orderBy('purchased_at', 'desc')
            ->paginate(20);

        return view('tickets.index', compact('tickets'));
    }
}