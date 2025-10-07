<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use App\Services\RaffleDrawService;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class AdminRaffleController extends Controller
{
    protected RaffleDrawService $drawService;
    protected PayoutService $payoutService;

    public function __construct(RaffleDrawService $drawService, PayoutService $payoutService)
    {
        // Middleware: Nur Admins dürfen hier rein
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });

        $this->drawService = $drawService;
        $this->payoutService = $payoutService;
    }

    /**
     * Admin Dashboard - Übersicht aller Raffles
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Raffle::with(['product', 'product.seller']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $raffles = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'scheduled' => Raffle::where('status', 'scheduled')->count(),
            'active' => Raffle::where('status', 'active')->count(),
            'pending_draw' => Raffle::where('status', 'pending_draw')->count(),
            'completed' => Raffle::where('status', 'completed')->count(),
            'cancelled' => Raffle::where('status', 'cancelled')->count(),
        ];

        return view('admin.raffles.index', compact('raffles', 'stats', 'status'));
    }

    /**
     * Raffle Details mit Draw-Optionen
     */
    public function show(Raffle $raffle)
    {
        $raffle->load(['product', 'product.seller', 'product.images', 'tickets', 'winnerTicket']);

        // Simulation für Preview
        $simulation = null;
        if ($raffle->status === 'pending_draw' || $raffle->status === 'active') {
            try {
                $simulation = $this->drawService->simulateDraw($raffle);
            } catch (\Exception $e) {
                $simulation = ['error' => $e->getMessage()];
            }
        }

        return view('admin.raffles.show', compact('raffle', 'simulation'));
    }

    /**
     * Manuell Raffle ziehen (für Livestream-Highlights)
     */
    public function draw(Raffle $raffle)
    {
        try {
            // Validierung
            if ($raffle->status !== 'pending_draw' && $raffle->status !== 'active') {
                return back()->with('error', "Raffle kann nicht gezogen werden. Status: {$raffle->status}");
            }

            // Force status auf pending_draw wenn noch active
            if ($raffle->status === 'active') {
                $raffle->update(['status' => 'pending_draw']);
            }

            $result = $this->drawService->drawRaffle($raffle);

            return redirect()
                ->route('admin.raffles.show', $raffle->id)
                ->with('success', 'Verlosung erfolgreich durchgeführt!')
                ->with('draw_result', $result);

        } catch (\Exception $e) {
            return back()->with('error', 'Fehler bei der Ziehung: ' . $e->getMessage());
        }
    }

    /**
     * Live-Drawing View (für Publikum sichtbar)
     */
    public function liveDrawing(Raffle $raffle)
    {
        if ($raffle->status !== 'pending_draw') {
            abort(404, 'Raffle ist nicht bereit für Live-Ziehung');
        }

        $raffle->load(['product', 'product.images', 'tickets']);

        return view('admin.raffles.live-drawing', compact('raffle'));
    }

    /**
     * API Endpoint: Ziehung für Live-Animation ausführen
     */
    public function executeLiveDraw(Request $request, Raffle $raffle)
    {
        try {
            $result = $this->drawService->drawRaffle($raffle);

            return response()->json([
                'success' => true,
                'winner' => [
                    'ticket_id' => $result['winner_ticket']->id,
                    'ticket_number' => $result['winner_ticket']->ticket_number,
                    'user_id' => $result['winner_ticket']->user_id,
                    'user_name' => $result['winner_ticket']->user->first_name ?? 'Anonym'
                ],
                'payout' => $result['payout_result']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Raffle abbrechen und refunden
     */
    public function cancel(Raffle $raffle)
    {
        try {
            if ($raffle->status === 'completed') {
                return back()->with('error', 'Abgeschlossene Raffles können nicht abgebrochen werden.');
            }

            $result = $this->payoutService->refundRaffle($raffle);

            return redirect()
                ->route('admin.raffles.index')
                ->with('success', "Raffle abgebrochen. {$result['refund_count']} Tickets refunded.");

        } catch (\Exception $e) {
            return back()->with('error', 'Fehler beim Abbruch: ' . $e->getMessage());
        }
    }

    /**
     * Raffle manuell starten (vor scheduled time)
     */
    public function start(Raffle $raffle)
    {
        if ($raffle->status !== 'scheduled') {
            return back()->with('error', 'Nur geplante Raffles können gestartet werden.');
        }

        $raffle->update(['status' => 'active']);

        return back()->with('success', 'Raffle manuell gestartet!');
    }

    /**
     * Raffle Status manuell ändern
     */
    public function updateStatus(Request $request, Raffle $raffle)
    {
        $request->validate([
            'status' => 'required|in:scheduled,active,pending_draw,completed,cancelled,refunded'
        ]);

        $raffle->update(['status' => $request->status]);

        return back()->with('success', 'Status aktualisiert!');
    }

    /**
     * Bulk Actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:start,cancel,draw',
            'raffle_ids' => 'required|array',
            'raffle_ids.*' => 'exists:raffles,id'
        ]);

        $raffles = Raffle::whereIn('id', $request->raffle_ids)->get();
        $successCount = 0;

        foreach ($raffles as $raffle) {
            try {
                switch ($request->action) {
                    case 'start':
                        if ($raffle->status === 'scheduled') {
                            $raffle->update(['status' => 'active']);
                            $successCount++;
                        }
                        break;

                    case 'cancel':
                        if ($raffle->status !== 'completed') {
                            $this->payoutService->refundRaffle($raffle);
                            $successCount++;
                        }
                        break;

                    case 'draw':
                        if ($raffle->status === 'pending_draw') {
                            $this->drawService->drawRaffle($raffle);
                            $successCount++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                // Log und weiter
                continue;
            }
        }

        return back()->with('success', "{$successCount} Raffle(s) erfolgreich verarbeitet.");
    }
}