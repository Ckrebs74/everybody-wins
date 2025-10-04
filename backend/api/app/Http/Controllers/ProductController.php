<?php

// =====================================================
// FILE: app/Http/Controllers/ProductController.php (UPDATE)
// =====================================================

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display listing page with categories and search
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'primaryImage', 'seller'])
            ->where('status', 'active')
            ->where('end_date', '>', now());

        // Category filter
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('ticket_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('ticket_price', 'desc');
                break;
            case 'ending_soon':
                $query->orderBy('end_date', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show product detail page with all images
     */
    public function show($id)
    {
        $product = Product::with(['images', 'seller', 'category', 'tickets.user'])
            ->findOrFail($id);

        // Get related products from same category
        $relatedProducts = Product::with('primaryImage')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Store new product with images
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'target_price' => 'required|numeric|min:10|max:10000',
            'ticket_price' => 'required|numeric|min:1|max:10',
            'end_date' => 'required|date|after:today',
            'condition' => 'required|in:new,like_new,good,acceptable',
            'brand' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:100',
            'shipping_cost' => 'required|numeric|min:0',
            'shipping_info' => 'nullable|string|max:500',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            'primary_image_index' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create([
                'seller_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'target_price' => $validated['target_price'],
                'ticket_price' => $validated['ticket_price'],
                'end_date' => $validated['end_date'],
                'status' => 'pending', // Admin must approve
                'decision_type' => 'automatic',
                'condition' => $validated['condition'],
                'brand' => $validated['brand'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'shipping_cost' => $validated['shipping_cost'],
                'shipping_info' => $validated['shipping_info'] ?? null
            ]);

            // Upload images
            if ($request->hasFile('images')) {
                $uploadedImages = $this->imageService->uploadMultiple(
                    $request->file('images'),
                    $product->id
                );

                $primaryIndex = $validated['primary_image_index'] ?? 0;

                foreach ($uploadedImages as $index => $imageData) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imageData['image_path'],
                        'thumbnail_path' => $imageData['thumbnail_path'],
                        'alt_text' => $product->title,
                        'sort_order' => $index,
                        'is_primary' => ($index === $primaryIndex)
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('products.show', $product->id)
                ->with('success', 'Produkt erfolgreich erstellt! Wartet auf Admin-Freigabe.');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded images if product creation failed
            if (isset($product)) {
                $this->imageService->deleteProductImages($product->id);
            }
            
            return back()
                ->withErrors(['error' => 'Fehler beim Erstellen des Produkts.'])
                ->withInput();
        }
    }

    /**
     * Update product images
     */
    public function updateImages(Request $request, $id)
    {
        $product = Product::where('seller_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',
            'primary_image_id' => 'nullable|exists:product_images,id'
        ]);

        DB::beginTransaction();

        try {
            // Delete selected images
            if (!empty($validated['delete_images'])) {
                ProductImage::whereIn('id', $validated['delete_images'])
                    ->where('product_id', $product->id)
                    ->delete();
            }

            // Upload new images
            if ($request->hasFile('images')) {
                $currentMaxOrder = $product->images()->max('sort_order') ?? -1;
                $uploadedImages = $this->imageService->uploadMultiple(
                    $request->file('images'),
                    $product->id
                );

                foreach ($uploadedImages as $index => $imageData) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imageData['image_path'],
                        'thumbnail_path' => $imageData['thumbnail_path'],
                        'alt_text' => $product->title,
                        'sort_order' => $currentMaxOrder + $index + 1,
                        'is_primary' => false
                    ]);
                }
            }

            // Update primary image
            if (!empty($validated['primary_image_id'])) {
                // Reset all images to non-primary
                $product->images()->update(['is_primary' => false]);
                // Set new primary
                $product->images()
                    ->where('id', $validated['primary_image_id'])
                    ->update(['is_primary' => true]);
            }

            DB::commit();

            return back()->with('success', 'Bilder erfolgreich aktualisiert!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Fehler beim Aktualisieren der Bilder.']);
        }
    }
}