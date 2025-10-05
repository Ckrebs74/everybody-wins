<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Transaction;
use App\Services\SpendingLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $spendingLimitService;

    public function __construct(SpendingLimitService $spendingLimitService)
    {
        $this->middleware('auth');
        $this->spendingLimitService = $spendingLimitService;
    }

    /**
     * User Dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Spending Statistics
        $spendingStats = $this->spendingLimitService->getStatistics($user->id);

        // Aktive Tickets
        $activeTickets = Ticket::with(['raffle.product.images'])
            ->where('user_id', $user->id)
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('purchased_at', 'desc')
            ->limit(5)
            ->get();

        // Anzahl Tickets pro Status
        $ticketCounts = [
            'active' => Ticket::where('user_id', $user->id)
                ->whereHas('raffle', fn($q) => $q->where('status', 'active'))
                ->count(),
            'winner' => Ticket::where('user_id', $user->id)
                ->where('status', 'winner')
                ->count(),
            'total' => Ticket::where('user_id', $user->id)->count(),
        ];

        // Letzte Transaktionen
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Gewinnchancen berechnen
        $winningChances = $this->calculateWinningChances($user->id);

        return view('dashboard', compact(
            'user',
            'spendingStats',
            'activeTickets',
            'ticketCounts',
            'recentTransactions',
            'winningChances'
        ));
    }

    /**
     * Berechne Gewinnchancen für aktive Tickets
     */
    private function calculateWinningChances(int $userId): array
    {
        $activeTickets = Ticket::with('raffle')
            ->where('user_id', $userId)
            ->whereHas('raffle', fn($q) => $q->where('status', 'active'))
            ->get();

        $chances = [];

        foreach ($activeTickets->groupBy('raffle_id') as $raffleId => $tickets) {
            $raffle = $tickets->first()->raffle;
            $userTicketCount = $tickets->count();
            $totalTickets = $raffle->tickets_sold;

            if ($totalTickets > 0) {
                $chance = ($userTicketCount / $totalTickets) * 100;
                
                $chances[] = [
                    'raffle_id' => $raffleId,
                    'product_title' => $raffle->product->title,
                    'user_tickets' => $userTicketCount,
                    'total_tickets' => $totalTickets,
                    'chance_percentage' => round($chance, 2),
                ];
            }
        }

        return $chances;
    }
}