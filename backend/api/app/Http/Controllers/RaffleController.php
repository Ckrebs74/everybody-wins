<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    /**
     * Display a listing of all active raffles
     */
    public function index(Request $request)
    {
        // ⚠️ WICHTIG: 'images' MUSS hier stehen!
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
     * Display the specified raffle
     */
    public function show($id)
    {
        // ⚠️ WICHTIG: Auch hier 'images' laden!
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

        return view('raffles.show', compact('product', 'relatedProducts'));
    }
}