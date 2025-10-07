<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use App\Models\Product;
use App\Services\RaffleDrawService;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRaffleController extends Controller
{
    protected RaffleDrawService $drawService;
    protected PayoutService $payoutService;

    public function __construct(RaffleDrawService $drawService, PayoutService $payoutService)
    {
        // MIDDLEWARE ENTFERNT - wird bereits in routes/web.php mit middleware(['auth', 'admin']) definiert
        // Die Middleware-Prüfung ist nicht mehr nötig, da sie bereits in den Routes erfolgt
        
        $this->drawService = $drawService;
        $this->payoutService = $payoutService;
    }

    /**
     * Admin Dashboard - Übersicht aller Verlosungen
     */
    public function index(Request $request)
    {
        $query = Raffle::with(['product.seller', 'product.category', 'product.images']);

        // Filter nach Status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Suche
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sortierung
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        $allowedSorts = ['created_at', 'starts_at', 'ends_at', 'tickets_sold', 'total_revenue'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $raffles = $query->paginate(20);

        // Aktueller Status-Filter
        $status = $request->get('status', 'all');

        // Statistiken
        $stats = [
            'total' => Raffle::count(),
            'scheduled' => Raffle::where('status', 'scheduled')->count(),
            'active' => Raffle::where('status', 'active')->count(),
            'pending_draw' => Raffle::where('status', 'pending_draw')->count(),
            'completed' => Raffle::where('status', 'completed')->count(),
            'cancelled' => Raffle::where('status', 'cancelled')->count(),
            'total_revenue' => Raffle::where('status', 'completed')->sum('total_revenue'),
        ];

        return view('admin.raffles.index', compact('raffles', 'stats', 'status'));
    }

    /**
     * Detailansicht einer Verlosung
     */
    public function show(Raffle $raffle)
    {
        $raffle->load([
            'product.seller',
            'product.category',
            'product.images',
            'tickets.user'
        ]);

        // Gewinner-Ticket laden falls vorhanden
        if ($raffle->winner_ticket_id) {
            $raffle->load('winnerTicket.user');
        }

        // Ticket-Statistiken
        $ticketStats = [
            'total_sold' => $raffle->tickets()->count(),
            'total_revenue' => $raffle->total_revenue,
            'unique_buyers' => $raffle->unique_participants,
            'avg_tickets_per_user' => $raffle->tickets()->count() > 0 && $raffle->unique_participants > 0
                ? round($raffle->tickets()->count() / $raffle->unique_participants, 2)
                : 0,
        ];

        // Top-Käufer
        $topBuyers = DB::table('tickets')
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->where('tickets.raffle_id', $raffle->id)
            ->select(
                'users.id',
                'users.email',
                'users.first_name',
                'users.last_name',
                DB::raw('COUNT(*) as ticket_count'),
                DB::raw('SUM(tickets.price) as total_spent')
            )
            ->groupBy('users.id', 'users.email', 'users.first_name', 'users.last_name')
            ->orderByDesc('ticket_count')
            ->limit(10)
            ->get()
            ->map(function ($buyer) {
                // Display Name: "Vorname Nachname" oder Email als Fallback
                $buyer->display_name = trim($buyer->first_name . ' ' . $buyer->last_name) ?: $buyer->email;
                return $buyer;
            });

        return view('admin.raffles.show', compact('raffle', 'ticketStats', 'topBuyers'));
    }

    /**
     * Live-Ziehung Ansicht
     */
    public function liveDrawing(Raffle $raffle)
    {
        // Prüfe ob die Verlosung bereit ist
        if ($raffle->status !== 'active') {
            return redirect()->route('admin.raffles.show', $raffle)
                ->with('error', 'Diese Verlosung kann nicht gezogen werden. Status: ' . $raffle->status);
        }

        if ($raffle->tickets()->count() === 0) {
            return redirect()->route('admin.raffles.show', $raffle)
                ->with('error', 'Es wurden keine Lose verkauft.');
        }

        $raffle->load(['product.images', 'tickets.user']);

        return view('admin.raffles.live-drawing', compact('raffle'));
    }

    /**
     * Live-Ziehung durchführen (AJAX)
     * GEFIXT: winner_ticket_id, status 'completed', winner_name
     */
    public function executeLiveDraw(Raffle $raffle)
    {
        try {
            DB::beginTransaction();

            // Validierungen
            if ($raffle->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Diese Verlosung ist nicht aktiv.'
                ], 400);
            }

            // BUG FIX 1: winner_ticket_id statt winner_id
            if ($raffle->winner_ticket_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diese Verlosung wurde bereits gezogen.'
                ], 400);
            }

            $tickets = $raffle->tickets()->with('user')->get();
            
            if ($tickets->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keine Lose verkauft.'
                ], 400);
            }

            // Zufälliges Ticket auswählen
            $winningTicket = $tickets->random();
            
            // BUG FIX 2: Nur winner_ticket_id setzen (nicht winner_id + winning_ticket_id)
            // BUG FIX 3: Status 'completed' statt 'drawn'
            $raffle->update([
                'winner_ticket_id' => $winningTicket->id,
                'drawn_at' => now(),
                'status' => 'completed'
            ]);

            // Auszahlung durchführen
            $this->payoutService->processRafflePayout($raffle);

            DB::commit();

            Log::info('Live-Ziehung erfolgreich', [
                'raffle_id' => $raffle->id,
                'winner_ticket_id' => $winningTicket->id,
                'winner_user_id' => $winningTicket->user_id
            ]);

            // BUG FIX 4: User hat first_name/last_name, nicht name
            $winnerName = trim($winningTicket->user->first_name . ' ' . $winningTicket->user->last_name);
            if (empty($winnerName)) {
                $winnerName = $winningTicket->user->email;
            }

            return response()->json([
                'success' => true,
                'winner' => [
                    'id' => $winningTicket->user_id,
                    'winner_name' => $winnerName,
                    'email' => $winningTicket->user->email,
                    'ticket_number' => $winningTicket->ticket_number,
                ],
                'raffle' => [
                    'id' => $raffle->id,
                    'status' => $raffle->status,
                    'drawn_at' => $raffle->drawn_at->format('d.m.Y H:i'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Fehler bei Live-Ziehung', [
                'raffle_id' => $raffle->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manuelle Ziehung (ohne Live-Animation)
     */
    public function draw(Raffle $raffle)
    {
        try {
            $result = $this->drawService->drawRaffle($raffle);
            
            $winnerName = $result['winner_ticket']->user->first_name ?? 'Gewinner';

            return redirect()
                ->route('admin.raffles.show', $raffle)
                ->with('success', 'Gewinner erfolgreich gezogen: ' . $winnerName);

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.raffles.show', $raffle)
                ->with('error', 'Fehler bei der Ziehung: ' . $e->getMessage());
        }
    }

    /**
     * Verlosung starten
     */
    public function start(Raffle $raffle)
    {
        if ($raffle->status !== 'pending') {
            return redirect()
                ->route('admin.raffles.show', $raffle)
                ->with('error', 'Diese Verlosung kann nicht gestartet werden.');
        }

        $raffle->update([
            'status' => 'active',
            'starts_at' => now()
        ]);

        return redirect()
            ->route('admin.raffles.show', $raffle)
            ->with('success', 'Verlosung wurde gestartet.');
    }

    /**
     * Verlosung abbrechen
     */
    public function cancel(Raffle $raffle)
    {
        try {
            DB::beginTransaction();

            // Rückerstattung aller Tickets
            foreach ($raffle->tickets as $ticket) {
                $ticket->user->increment('wallet_balance', $ticket->price);
                
                // Transaction erstellen
                \App\Models\Transaction::create([
                    'user_id' => $ticket->user_id,
                    'type' => 'refund',
                    'amount' => $ticket->price,
                    'balance_before' => $ticket->user->wallet_balance - $ticket->price,
                    'balance_after' => $ticket->user->wallet_balance,
                    'reference_type' => 'raffle',
                    'reference_id' => (string)$raffle->id,
                    'status' => 'completed',
                    'description' => 'Rückerstattung: Verlosung abgebrochen - ' . $raffle->product->title
                ]);
            }

            $raffle->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()
                ->route('admin.raffles.show', $raffle)
                ->with('success', 'Verlosung wurde abgebrochen. Alle Teilnehmer wurden erstattet.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.raffles.show', $raffle)
                ->with('error', 'Fehler beim Abbrechen: ' . $e->getMessage());
        }
    }

    /**
     * Status aktualisieren
     */
    public function updateStatus(Raffle $raffle, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,active,drawn,completed,cancelled'
        ]);

        $raffle->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status aktualisiert'
        ]);
    }

    /**
     * Bulk-Aktionen
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,cancel,delete',
            'raffle_ids' => 'required|array',
            'raffle_ids.*' => 'exists:raffles,id'
        ]);

        $raffles = Raffle::whereIn('id', $request->raffle_ids)->get();
        $count = 0;

        foreach ($raffles as $raffle) {
            switch ($request->action) {
                case 'activate':
                    if ($raffle->status === 'pending') {
                        $raffle->update(['status' => 'active', 'starts_at' => now()]);
                        $count++;
                    }
                    break;
                    
                case 'cancel':
                    if (in_array($raffle->status, ['pending', 'active'])) {
                        $this->cancel($raffle);
                        $count++;
                    }
                    break;
                    
                case 'delete':
                    if ($raffle->status === 'cancelled' && $raffle->tickets()->count() === 0) {
                        $raffle->delete();
                        $count++;
                    }
                    break;
            }
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('success', "{$count} Verlosungen wurden aktualisiert.");
    }
}