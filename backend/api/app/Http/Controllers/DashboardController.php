<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Services\SpendingLimitService;

class DashboardController extends Controller
{
    protected $spendingLimitService;

    public function __construct(SpendingLimitService $spendingLimitService)
    {
        // MIDDLEWARE ENTFERNT - wird bereits in routes/web.php definiert
        $this->spendingLimitService = $spendingLimitService;
    }

    /**
     * User Dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Spending Statistics
        $remainingBudget = $this->spendingLimitService->getRemainingBudget($user->id);
        $spentThisHour = 10 - $remainingBudget;
        
        // User Statistics
        $stats = [
            'wallet_balance' => $user->wallet_balance ?? 0,
            'total_tickets' => Ticket::where('user_id', $user->id)->count(),
            'active_raffles' => Ticket::where('user_id', $user->id)
                ->whereHas('product.raffle', function($query) {
                    $query->where('status', 'active');
                })
                ->distinct('product_id')
                ->count(),
            'total_spent' => $user->total_spent ?? 0,
            'spent_this_hour' => $spentThisHour,
            'remaining_budget' => $remainingBudget,
        ];

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Active Tickets
        $activeTickets = Ticket::where('user_id', $user->id)
            ->whereHas('product.raffle', function($query) {
                $query->where('status', 'active');
            })
            ->with(['product.images', 'product.raffle'])
            ->get()
            ->groupBy('product_id')
            ->map(function($tickets) {
                return [
                    'product' => $tickets->first()->product,
                    'ticket_count' => $tickets->count(),
                    'total_spent' => $tickets->count() * 1, // 1€ pro Los
                    'win_chance' => $this->calculateWinChance($tickets->first()->product, $tickets->count())
                ];
            });

        return view('dashboard', compact('user', 'stats', 'recentTransactions', 'activeTickets'));
    }

    /**
     * Calculate winning chance
     */
    private function calculateWinChance($product, $userTicketCount)
    {
        $totalTickets = Ticket::where('product_id', $product->id)->count();
        
        if ($totalTickets === 0) {
            return 0;
        }

        return round(($userTicketCount / $totalTickets) * 100, 2);
    }
}