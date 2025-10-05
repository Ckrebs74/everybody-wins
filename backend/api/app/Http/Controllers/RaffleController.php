<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\SpendingLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaffleController extends Controller
{
    protected $spendingLimitService;

    public function __construct(SpendingLimitService $spendingLimitService)
    {
        $this->spendingLimitService = $spendingLimitService;
    }

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

        // Sort
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('target_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('target_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('raffles.index', compact('products', 'categories'));
    }

    /**
     * Display the specified raffle
     */
    public function show($id)
    {
        $product = Product::with(['images', 'category', 'seller', 'raffle.tickets'])
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
            $remainingBudget = $this->spendingLimitService->getRemainingBudget(Auth::id());
            $currentHourSpending = 10 - $remainingBudget;
        }

        // Raffle Statistiken
        $raffle = $product->raffle;
        $ticketsSold = $raffle->tickets_sold;
        $totalTarget = $raffle->total_target;
        $currentRevenue = $ticketsSold * 1; // Jedes Los kostet 1€
        $progressPercentage = $totalTarget > 0 ? ($currentRevenue / $totalTarget) * 100 : 0;

        return view('raffles.show', compact(
            'product', 
            'relatedProducts', 
            'remainingBudget', 
            'currentHourSpending',
            'ticketsSold',
            'totalTarget',
            'currentRevenue',
            'progressPercentage'
        ));
    }
}