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
                ->whereHas('raffle', function($query) {
                    $query->where('status', 'active');
                })
                ->distinct('raffle_id')
                ->count(),
            'total_spent' => $user->total_spent ?? 0,
            'spent_this_hour' => $spentThisHour,
            'remaining_budget' => $remainingBudget,
        ];

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Active Tickets
        $activeTickets = Ticket::where('user_id', $user->id)
            ->whereHas('raffle', function($query) {
                $query->where('status', 'active');
            })
            ->with(['raffle.product.images'])
            ->get()
            ->groupBy('raffle_id')
            ->map(function($tickets) {
                $raffle = $tickets->first()->raffle;
                return [
                    'product' => $raffle->product,
                    'raffle' => $raffle,
                    'ticket_count' => $tickets->count(),
                    'total_spent' => $tickets->count() * 1, // 1€ pro Los
                    'win_chance' => $this->calculateWinChance($raffle, $tickets->count())
                ];
            });

        return view('dashboard', compact('user', 'stats', 'recentTransactions', 'activeTickets'));
    }

    /**
     * Calculate winning chance
     */
    private function calculateWinChance($raffle, $userTicketCount)
    {
        $totalTickets = Ticket::where('raffle_id', $raffle->id)->count();
        
        if ($totalTickets === 0) {
            return 0;
        }

        return round(($userTicketCount / $totalTickets) * 100, 2);
    }
}