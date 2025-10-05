<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Raffle;
use App\Models\Category;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    /**
     * Display a listing of all active raffles
     */
    public function index(Request $request)
    {
        // Lade Products mit ihren Raffles und Bildern
        $query = Product::with(['category', 'images', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->where('status', 'active');

        // Category filter
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sorting
        switch ($request->input('sort', 'newest')) {
            case 'ending_soon':
                $query->join('raffles', 'products.id', '=', 'raffles.product_id')
                      ->orderBy('raffles.ends_at', 'asc')
                      ->select('products.*');
                break;
            case 'price_low':
                $query->orderBy('target_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('target_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        // Categories mit Produkt-Count
        $categories = Category::withCount(['products' => function($q) {
            $q->whereHas('raffle', function($raffle) {
                $raffle->where('status', 'active');
            })->where('status', 'active');
        }])->where('is_active', true)->get();

        return view('raffles.index', compact('products', 'categories'));
    }

    /**
     * Display the specified raffle (via Product Slug)
     */
    public function show($slug)
    {
        // Lade Product mit Slug
        $product = Product::with(['category', 'images', 'seller', 'raffle', 'raffle.tickets'])
            ->where('slug', $slug)
            ->firstOrFail();
        
        $raffle = $product->raffle;
        
        // Prüfe ob Raffle existiert
        if (!$raffle) {
            abort(404, 'Keine aktive Verlosung für dieses Produkt gefunden');
        }

        // View Counter erhöhen
        $product->increment('view_count');

        // Statistiken für die Anzeige
        $stats = [
            'tickets_sold' => $raffle->tickets_sold,
            'total_revenue' => $raffle->total_revenue,
            'unique_participants' => $raffle->unique_participants,
            'progress_percentage' => ($raffle->total_target > 0) 
                ? round(($raffle->total_revenue / $raffle->total_target) * 100, 2) 
                : 0,
            'target_reached' => $raffle->target_reached,
            'days_remaining' => now()->diffInDays($raffle->ends_at, false),
        ];

        // Ähnliche Produkte (gleiche Kategorie)
        $relatedProducts = Product::with(['category', 'images', 'raffle'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Letzte Teilnehmer (anonymisiert)
        $recentParticipants = $raffle->tickets()
            ->with('user')
            ->orderBy('purchased_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($ticket) {
                $email = $ticket->user->email;
                return [
                    'email' => substr($email, 0, 3) . '***' . substr($email, -8),
                    'time' => $ticket->purchased_at->diffForHumans()
                ];
            });

        return view('raffles.show', compact('product', 'raffle', 'stats', 'relatedProducts', 'recentParticipants'));
    }
}