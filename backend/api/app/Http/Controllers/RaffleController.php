<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    public function index(Request $request)
    {
        // WICHTIG: 'images' muss in der with() Anweisung sein!
        $query = Product::with(['category', 'images', 'raffle', 'seller'])
            ->whereHas('raffle', function($q) {
                $q->where('status', 'active');
            })
            ->where('status', 'active');

        // Sortierung
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
            default: // newest
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();

        return view('raffles.index', compact('products', 'categories'));
    }

    public function show($id)
    {
        // Auch hier: 'images' laden!
        $product = Product::with(['images', 'seller', 'category', 'raffle'])
            ->findOrFail($id);
        
        // View Count erhöhen
        $product->increment('view_count');
        
        return view('raffles.show', compact('product'));
    }
}