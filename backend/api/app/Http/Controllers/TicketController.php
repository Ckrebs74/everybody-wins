<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Services\WalletService;
use App\Services\SpendingLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    protected $walletService;
    protected $spendingLimitService;

    public function __construct(
        WalletService $walletService,
        SpendingLimitService $spendingLimitService
    ) {
        // Middleware wird in routes/web.php definiert
        $this->walletService = $walletService;
        $this->spendingLimitService = $spendingLimitService;
    }

    /**
     * Purchase tickets for a raffle
     */
    public function purchase(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $user = Auth::user();
        $product = Product::with('raffle')->findOrFail($productId);
        $quantity = $request->quantity;
        $totalCost = $quantity * 1; // 1€ pro Los

        // Validierungen
        if ($product->raffle->status !== 'active') {
            return back()->with('error', 'Diese Verlosung ist nicht mehr aktiv.');
        }

        // Spending Limit Check
        if (!$this->spendingLimitService->canSpend($user->id, $totalCost)) {
            $remaining = $this->spendingLimitService->getRemainingBudget($user->id);
            return back()->with('error', "Ausgabenlimit erreicht! Sie können in dieser Stunde noch {$remaining}€ ausgeben.");
        }

        // Wallet Balance Check
        if ($user->wallet_balance < $totalCost) {
            return redirect()->route('wallet.index')
                ->with('error', 'Nicht genug Guthaben. Bitte laden Sie Ihr Wallet auf.');
        }

        try {
            DB::beginTransaction();

            // Create tickets
            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $tickets[] = Ticket::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'ticket_number' => $this->generateTicketNumber(),
                    'purchase_price' => 1.00,
                    'status' => 'active',
                ]);
            }

            // Update wallet
            $this->walletService->deductBalance($user->id, $totalCost, $product->id, 'ticket_purchase');

            // Update spending limit
            $this->spendingLimitService->addSpending($user->id, $totalCost);

            // Update raffle stats
            $raffle = $product->raffle;
            $raffle->tickets_sold += $quantity;
            $raffle->current_amount += $totalCost;
            
            // Check if target reached
            if ($raffle->current_amount >= $raffle->end_price) {
                $raffle->status = 'completed';
            }
            
            $raffle->save();

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', "Erfolgreich {$quantity} Los(e) für {$product->title} gekauft!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Fehler beim Loskauf: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber()
    {
        do {
            $number = 'TKT-' . strtoupper(uniqid());
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Show user's tickets
     */
    public function index()
    {
        $user = Auth::user();
        
        $tickets = Ticket::where('user_id', $user->id)
            ->with(['product.images', 'product.raffle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('tickets.index', compact('tickets'));
    }
}