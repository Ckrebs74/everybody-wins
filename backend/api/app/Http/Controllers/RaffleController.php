<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SpendingLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaffleController extends Controller
{
    /**
     * Display a listing of all active raffles
     */
    public function index(Request $request)
    {
        // Lade 'images' mit allen anderen Relationships
        $query = Product::with(['category', 'images', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->where('status', 'active');

        // Category filter
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category_id', $request->category);
        }

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'ending':
                $query->whereHas('raffle', function($q) {
                    $q->orderBy('ends_at', 'asc');
                });
                break;
            case 'price_low':
                $query->orderBy('target_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('target_price', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();

        return view('raffles.index', compact('products', 'categories'));
    }

    /**
     * Display the specified raffle with spending limit info
     */
    public function show($id)
    {
        // Lade das Produkt mit allen Relationships
        $product = Product::with(['images', 'category', 'seller', 'raffle', 'raffle.tickets'])
            ->findOrFail($id);

        // Increment view count
        $product->increment('view_count');

        // Get related products
        $relatedProducts = Product::with(['images', 'raffle'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        // Berechne verfügbares Budget für eingeloggten User
        $remainingBudget = 10; // Standard für nicht eingeloggte User
        $currentHourSpending = 0;
        
        if (Auth::check()) {
            $remainingBudget = SpendingLimit::getRemainingBudget(Auth::id());
            $currentHourSpending = 10 - $remainingBudget;
        }

        return view('raffles.show', compact('product', 'relatedProducts', 'remainingBudget', 'currentHourSpending'));
    }

    /**
     * Process ticket purchase with spending limit check
     */
    public function buyTickets(Request $request, $id)
    {
        // User muss eingeloggt sein
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bitte melden Sie sich an, um Lose zu kaufen.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $quantity = $request->quantity;
        $userId = Auth::id();

        // Prüfe Ausgabenlimit
        if (!SpendingLimit::canSpend($userId, $quantity)) {
            $remaining = SpendingLimit::getRemainingBudget($userId);
            return back()->with('error', 'Ausgabenlimit überschritten! Sie können in dieser Stunde noch maximal ' . number_format($remaining, 2, ',', '.') . '€ ausgeben.');
        }

        $product = Product::with('raffle')->findOrFail($id);
        
        if (!$product->raffle || $product->raffle->status !== 'active') {
            return back()->with('error', 'Diese Verlosung ist nicht aktiv.');
        }

        // TODO: Stripe Payment Integration
        // Für Demo-Zwecke simulieren wir den Kauf

        try {
            // Beginne Transaktion
            \DB::beginTransaction();

            // Erstelle Tickets
            for ($i = 0; $i < $quantity; $i++) {
                \App\Models\Ticket::create([
                    'raffle_id' => $product->raffle->id,
                    'user_id' => $userId,
                    'ticket_number' => strtoupper(uniqid('TICKET_')),
                    'price' => 1.00,
                    'status' => 'valid',
                    'is_bonus_ticket' => false
                ]);
            }

            // Update Raffle-Statistiken
            $product->raffle->increment('tickets_sold', $quantity);
            $product->raffle->increment('total_revenue', $quantity);
            
            // Prüfe ob User neu ist für diese Raffle
            $existingTickets = \App\Models\Ticket::where('raffle_id', $product->raffle->id)
                ->where('user_id', $userId)
                ->count();
            
            if ($existingTickets == $quantity) {
                // Neuer Teilnehmer
                $product->raffle->increment('unique_participants');
            }

            // Aktualisiere Ausgabenlimit
            SpendingLimit::addSpending($userId, $quantity);

            // Erstelle Transaktion
            \App\Models\Transaction::create([
                'user_id' => $userId,
                'type' => 'ticket_purchase',
                'amount' => $quantity,
                'balance_before' => 0, // TODO: Wallet-Integration
                'balance_after' => 0,  // TODO: Wallet-Integration
                'reference_type' => 'raffle',
                'reference_id' => $product->raffle->id,
                'status' => 'completed',
                'description' => "Kauf von {$quantity} Los(en) für {$product->title}"
            ]);

            \DB::commit();

            return back()->with('success', "Erfolgreich {$quantity} Los(e) gekauft! Viel Glück bei der Verlosung!");

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Ticket purchase failed: ' . $e->getMessage());
            return back()->with('error', 'Fehler beim Kauf der Lose. Bitte versuchen Sie es erneut.');
        }
    }
}