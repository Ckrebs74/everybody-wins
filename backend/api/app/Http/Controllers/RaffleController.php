<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Raffle;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    /**
     * Display a listing of all active raffles
     */
    public function index(Request $request)
    {
        // WICHTIG: Lade 'images' mit allen anderen Relationships!
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
        switch ($request->get('sort', 'newest')) {
            case 'price_low':
                $query->orderBy('target_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('target_price', 'desc');
                break;
            case 'popular':
                // Join mit raffles für Sortierung nach tickets_sold
                $query->select('products.*')
                      ->leftJoin('raffles', 'products.id', '=', 'raffles.product_id')
                      ->orderBy('raffles.tickets_sold', 'desc');
                break;
            case 'ending_soon':
                $query->select('products.*')
                      ->leftJoin('raffles', 'products.id', '=', 'raffles.product_id')
                      ->orderBy('raffles.ends_at', 'asc');
                break;
            default:
                $query->orderBy('products.created_at', 'desc');
        }

        // Paginate
        $products = $query->paginate(12)->withQueryString();
        
        // Get categories with count
        $categories = Category::withCount(['products' => function($q) {
            $q->where('status', 'active')
              ->whereHas('raffle', function($r) {
                  $r->where('status', 'active');
              });
        }])->get();

        return view('raffles.index', compact('products', 'categories'));
    }

    /**
     * Display the specified raffle
     */
    public function show($id)
    {
        // Lade Product mit allen notwendigen Relationships
        $product = Product::with([
            'images', 
            'seller', 
            'category', 
            'raffle.tickets' => function($q) {
                $q->with('user:id,email')
                  ->latest('purchased_at')
                  ->limit(20);
            }
        ])->findOrFail($id);

        // Check if raffle exists and is viewable
        if (!$product->raffle) {
            abort(404, 'Diese Verlosung existiert nicht.');
        }

        if ($product->raffle->status !== 'active' && $product->seller_id !== Auth::id()) {
            abort(404, 'Diese Verlosung ist nicht verfügbar.');
        }

        // Get related products from same category (mit Bildern!)
        $relatedProducts = Product::with(['images', 'raffle'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        // Get recent participants (anonymized)
        $recentParticipants = collect([]);
        if ($product->raffle && $product->raffle->tickets) {
            $recentParticipants = $product->raffle->tickets->map(function ($ticket) {
                if ($ticket->user) {
                    $emailParts = explode('@', $ticket->user->email);
                    $name = $emailParts[0] ?? 'User';
                    return [
                        'name' => substr($name, 0, 1) . '***',
                        'time' => $ticket->purchased_at ? $ticket->purchased_at->diffForHumans() : 'kürzlich'
                    ];
                }
                return null;
            })->filter();
        }

        // Calculate statistics
        $stats = [
            'tickets_sold' => $product->raffle->tickets_sold ?? 0,
            'progress_percentage' => $this->calculateProgress($product->raffle),
            'estimated_value' => $product->target_price ?? 0,
            'participants' => $product->raffle->unique_participants ?? 0,
            'total_revenue' => $product->raffle->total_revenue ?? 0,
            'ends_at' => $product->raffle->ends_at ?? null,
            'total_target' => $product->raffle->total_target ?? 0
        ];

        return view('raffles.show', compact('product', 'relatedProducts', 'recentParticipants', 'stats'));
    }

    /**
     * Helper: Calculate progress percentage
     */
    private function calculateProgress($raffle)
    {
        if (!$raffle || !$raffle->total_target || $raffle->total_target <= 0) {
            return 0;
        }
        
        $progress = ($raffle->total_revenue / $raffle->total_target) * 100;
        return min(100, round($progress, 2));
    }

    /**
     * Show raffles by category
     */
    public function category($categorySlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        
        $products = Product::with(['images', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->where('category_id', $category->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $categories = Category::withCount(['products' => function($q) {
            $q->where('status', 'active')
              ->whereHas('raffle', function($r) {
                  $r->where('status', 'active');
              });
        }])->get();

        return view('raffles.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category
        ]);
    }

    /**
     * Show raffles ending soon
     */
    public function endingSoon()
    {
        $products = Product::with(['images', 'category', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active')
                  ->where('ends_at', '>', now())
                  ->where('ends_at', '<', now()->addHours(24));
            })
            ->where('status', 'active')
            ->select('products.*')
            ->leftJoin('raffles', 'products.id', '=', 'raffles.product_id')
            ->orderBy('raffles.ends_at', 'asc')
            ->paginate(12);

        $categories = Category::withCount(['products' => function($q) {
            $q->where('status', 'active')
              ->whereHas('raffle', function($r) {
                  $r->where('status', 'active');
              });
        }])->get();

        return view('raffles.index', [
            'products' => $products,
            'categories' => $categories,
            'pageTitle' => '⏰ Bald endende Verlosungen'
        ]);
    }

    /**
     * Show popular raffles
     */
    public function popular()
    {
        $products = Product::with(['images', 'category', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active')
                  ->where('tickets_sold', '>', 0);
            })
            ->where('status', 'active')
            ->select('products.*')
            ->leftJoin('raffles', 'products.id', '=', 'raffles.product_id')
            ->orderBy('raffles.tickets_sold', 'desc')
            ->paginate(12);

        $categories = Category::withCount(['products' => function($q) {
            $q->where('status', 'active')
              ->whereHas('raffle', function($r) {
                  $r->where('status', 'active');
              });
        }])->get();

        return view('raffles.index', [
            'products' => $products,
            'categories' => $categories,
            'pageTitle' => '🔥 Beliebte Verlosungen'
        ]);
    }
}