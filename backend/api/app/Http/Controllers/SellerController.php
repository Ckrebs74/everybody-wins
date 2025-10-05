<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Raffle;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Verkäufer-Dashboard Übersicht
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Basis-Statistiken
        $stats = [
            'total_products' => Product::where('seller_id', $user->id)->count(),
            'active_raffles' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })->where('status', 'active')->count(),
            
            'draft_products' => Product::where('seller_id', $user->id)
                ->where('status', 'draft')->count(),
                
            'completed_raffles' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })->where('status', 'completed')->count(),
            
            // Einnahmen (nur aus abgeschlossenen Verlosungen, bei denen Zielpreis erreicht wurde)
            'total_earnings' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'completed')
            ->where('target_reached', true)
            ->sum('target_price'),
            
            // Durchschnittlicher Verkaufspreis
            'avg_sale_price' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'completed')
            ->where('target_reached', true)
            ->avg('target_price'),
        ];
        
        // Letzte 5 aktive Verlosungen
        $recentRaffles = Raffle::with(['product', 'product.images'])
            ->whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Benachrichtigungen über bald endende Verlosungen
        $endingSoon = Raffle::with(['product'])
            ->whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(2))
            ->orderBy('ends_at', 'asc')
            ->get();
        
        return view('seller.dashboard', compact('stats', 'recentRaffles', 'endingSoon'));
    }
    
    /**
     * Produktliste für Verkäufer
     */
    public function products(Request $request)
    {
        $user = Auth::user();
        
        // Base Query mit Eager Loading
        $query = Product::with(['category', 'images', 'raffle'])
            ->where('seller_id', $user->id);
        
        // Filter nach Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter nach Kategorie
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }
        
        // Suche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        
        // Sortierung
        switch ($request->input('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_high':
                $query->orderBy('target_price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('target_price', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        // Kategorien für Filter
        $categories = Category::withCount(['products' => function($q) use ($user) {
            $q->where('seller_id', $user->id);
        }])->where('is_active', true)->get();
        
        return view('seller.products.index', compact('products', 'categories'));
    }
    
    /**
     * Detailansicht eines Produkts für den Verkäufer
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $product = Product::with(['category', 'images', 'raffle', 'raffle.tickets'])
            ->where('seller_id', $user->id)
            ->findOrFail($id);
            
        // Statistiken für dieses spezifische Produkt
        $raffle = $product->raffle;
        
        if ($raffle) {
            $stats = [
                'tickets_sold' => $raffle->tickets_sold,
                'total_revenue' => $raffle->total_revenue,
                'unique_participants' => $raffle->unique_participants,
                'progress_percentage' => ($raffle->total_target > 0) 
                    ? round(($raffle->total_revenue / $raffle->total_target) * 100, 2) 
                    : 0,
                'days_remaining' => now()->diffInDays($raffle->ends_at, false),
                'target_reached' => $raffle->target_reached,
            ];
            
            // Letzte Ticket-Käufe
            $recentTickets = $raffle->tickets()
                ->with('user')
                ->orderBy('purchased_at', 'desc')
                ->take(10)
                ->get();
        } else {
            $stats = null;
            $recentTickets = collect();
        }
        
        return view('seller.products.show', compact('product', 'stats', 'recentTickets'));
    }
    
    /**
     * Analytics & Reporting
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        // Zeitraum (Standard: letzte 30 Tage)
        $startDate = $request->input('start_date', now()->subDays(30));
        $endDate = $request->input('end_date', now());
        
        // Umsatz nach Tag
        $dailyRevenue = Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'completed')
            ->where('target_reached', true)
            ->whereBetween('drawn_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(drawn_at) as date'),
                DB::raw('SUM(target_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        // Top-Kategorien
        $topCategories = Product::where('seller_id', $user->id)
            ->whereHas('raffle', function($q) {
                $q->where('status', 'completed')
                  ->where('target_reached', true);
            })
            ->with('category')
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
            
        // Performance-Metriken
        $metrics = [
            'conversion_rate' => $this->calculateConversionRate($user->id),
            'avg_tickets_per_raffle' => $this->calculateAvgTicketsPerRaffle($user->id),
            'success_rate' => $this->calculateSuccessRate($user->id),
        ];
        
        return view('seller.analytics', compact('dailyRevenue', 'topCategories', 'metrics'));
    }
    
    /**
     * Hilfsmethode: Konversionsrate berechnen
     */
    private function calculateConversionRate($sellerId)
    {
        $total = Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->count();
        
        if ($total === 0) return 0;
        
        $completed = Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->where('status', 'completed')
        ->where('target_reached', true)
        ->count();
        
        return round(($completed / $total) * 100, 2);
    }
    
    /**
     * Hilfsmethode: Durchschnittliche Tickets pro Verlosung
     */
    private function calculateAvgTicketsPerRaffle($sellerId)
    {
        return Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->where('status', 'completed')
        ->avg('tickets_sold') ?? 0;
    }
    
    /**
     * Hilfsmethode: Erfolgsquote (Zielpreis erreicht)
     */
    private function calculateSuccessRate($sellerId)
    {
        $total = Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->whereIn('status', ['completed', 'active'])
        ->count();
        
        if ($total === 0) return 0;
        
        $successful = Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->where('target_reached', true)
        ->count();
        
        return round(($successful / $total) * 100, 2);
    }
}