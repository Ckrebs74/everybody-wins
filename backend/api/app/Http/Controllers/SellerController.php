<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Raffle;
use App\Services\ProductCreationService;
use App\Services\MediaUploadService;
use App\Services\PriceSuggestionService;
use App\Http\Requests\{
    ProductStep1Request,
    ProductStep2Request,
    ProductStep3Request,
    ProductStep4Request,
    ProductStep5Request
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function __construct(
        private ProductCreationService $productService,
        private MediaUploadService $mediaService,
        private PriceSuggestionService $priceService
    ) {
        $this->middleware(['auth', 'seller']);
    }
    
    /**
     * DASHBOARD - Verkäufer-Übersicht
     * GET /seller/dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Statistiken berechnen
        $stats = [
            'active_raffles' => Product::where('seller_id', $user->id)
                ->whereIn('status', ['active', 'scheduled'])
                ->count(),
            
            'total_revenue' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->where('status', 'completed')
            ->where('target_reached', true)
            ->sum('target_price'),
            
            'total_tickets' => Raffle::whereHas('product', function($q) use ($user) {
                $q->where('seller_id', $user->id);
            })->sum('tickets_sold'),
            
            'success_rate' => $this->calculateSuccessRate($user->id),
        ];
        
        // Aktive Produkte mit Raffles und Bildern
        $activeProducts = Product::where('seller_id', $user->id)
            ->whereIn('status', ['active', 'scheduled'])
            ->with(['raffle', 'images' => function($q) {
                $q->orderBy('sort_order');
            }])
            ->latest()
            ->get();
        
        // Entwürfe
        $draftProducts = Product::where('seller_id', $user->id)
            ->where('status', 'draft')
            ->with('images')
            ->latest()
            ->get();
        
        // Abgeschlossene Produkte
        $completedProducts = Product::where('seller_id', $user->id)
            ->where('status', 'completed')
            ->with(['raffle', 'images' => function($q) {
                $q->orderBy('sort_order')->limit(1);
            }])
            ->latest()
            ->take(10)
            ->get();
        
        return view('seller.dashboard', compact(
            'stats',
            'activeProducts',
            'draftProducts',
            'completedProducts'
        ));
    }
    
    /**
     * PRODUKTLISTE - Alle Produkte des Verkäufers
     * GET /seller/products
     */
    public function products(Request $request)
    {
        $query = Product::where('seller_id', Auth::id())
            ->with(['category', 'raffle', 'images']);
        
        // Filter nach Status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Suche
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter nach Kategorie
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Sortierung
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();
        
        return view('seller.products.index', compact('products', 'categories'));
    }
    
    /**
     * PRODUKTDETAILS - Einzelnes Produkt anzeigen
     * GET /seller/products/{id}
     */
    public function show($id)
    {
        $product = Product::where('id', $id)
            ->where('seller_id', Auth::id())
            ->with(['category', 'raffle', 'images' => function($q) {
                $q->orderBy('sort_order');
            }])
            ->firstOrFail();
        
        return view('seller.products.show', compact('product'));
    }
    
    /**
     * ANALYTICS - Detaillierte Statistiken
     * GET /seller/analytics
     */
    public function analytics()
    {
        $user = Auth::user();
        
        // Tägliche Einnahmen (letzte 30 Tage)
        $dailyRevenue = Raffle::whereHas('product', function($q) use ($user) {
            $q->where('seller_id', $user->id);
        })
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subDays(30))
        ->selectRaw('DATE(created_at) as date, SUM(total_revenue) as revenue')
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        // Top-Kategorien
        $topCategories = Product::where('seller_id', $user->id)
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('count')
            ->take(5)
            ->get();
        
        // Weitere Metriken
        $metrics = [
            'conversion_rate' => $this->calculateConversionRate($user->id),
            'avg_tickets_per_raffle' => $this->calculateAvgTicketsPerRaffle($user->id),
            'success_rate' => $this->calculateSuccessRate($user->id),
        ];
        
        return view('seller.analytics', compact('dailyRevenue', 'topCategories', 'metrics'));
    }
    
    // ============================================
    // MULTI-STEP PRODUKTERSTELLUNG
    // ============================================
    
    /**
     * SCHRITT 1: Kategorie & Typ
     * GET /seller/products/create
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('seller.products.create.step1-category', [
            'categories' => $categories,
            'step' => 1,
        ]);
    }
    
    /**
     * SCHRITT 1: Speichern & weiter zu Schritt 2
     * POST /seller/products/create/step1
     */
    public function storeStep1(ProductStep1Request $request)
    {
        $validated = $request->validated();
        
        // Erstelle oder hole Draft
        $product = $this->productService->getOrCreateDraft(
            Auth::id(),
            session('product_draft_id')
        );
        
        // Speichere Schritt 1
        $product = $this->productService->saveStep1($product, $validated);
        
        // Speichere Draft-ID in Session
        session(['product_draft_id' => $product->id]);
        
        return redirect()->route('seller.products.create.step', ['step' => 2])
            ->with('success', 'Kategorie gespeichert');
    }
    
    /**
     * SCHRITT 2-5: Schritte anzeigen
     * GET /seller/products/create/step/{step}
     */
    public function showStep(int $step)
    {
        $draftId = session('product_draft_id');
        
        if (!$draftId) {
            return redirect()->route('seller.products.create')
                ->with('error', 'Bitte starten Sie mit Schritt 1');
        }
        
        $product = Product::where('id', $draftId)
            ->where('seller_id', Auth::id())
            ->firstOrFail();
        
        // Route zu korrektem View
        $views = [
            2 => 'seller.products.create.step2-details',
            3 => 'seller.products.create.step3-media',
            4 => 'seller.products.create.step4-pricing',
            5 => 'seller.products.create.step5-preview',
        ];
        
        if (!isset($views[$step])) {
            abort(404);
        }
        
        $data = [
            'product' => $product,
            'step' => $step,
        ];
        
        // Zusätzliche Daten für spezifische Schritte
        if ($step === 4) {
            $data['priceSuggestion'] = $this->priceService->suggest($product);
        }
        
        return view($views[$step], $data);
    }
    
    /**
     * SCHRITT 2: Produktdetails speichern
     * POST /seller/products/create/step2
     */
    public function storeStep2(ProductStep2Request $request)
    {
        $validated = $request->validated();
        $product = Product::where('id', session('product_draft_id'))
            ->where('seller_id', Auth::id())
            ->firstOrFail();
        
        $product = $this->productService->saveStep2($product, $validated);
        
        return redirect()->route('seller.products.create.step', ['step' => 3])
            ->with('success', 'Produktdetails gespeichert');
    }
    
    /**
     * SCHRITT 3: Media-Upload
     * POST /seller/products/create/step3
     */
    public function storeStep3(ProductStep3Request $request)
    {
        $validated = $request->validated();
        $product = Product::where('id', session('product_draft_id'))
            ->where('seller_id', Auth::id())
            ->firstOrFail();
        
        try {
            // Upload alle Medien
            if ($request->hasFile('media')) {
                $mediaFiles = $request->file('media');
                $sortOrder = $product->images()->max('sort_order') ?? 0;
                $sortOrder++;
                
                foreach ($mediaFiles as $index => $file) {
                    $isPrimary = ($index === 0 && $product->images()->count() === 0);
                    
                    $this->mediaService->uploadMedia(
                        $file,
                        $product,
                        $sortOrder + $index,
                        $isPrimary
                    );
                }
            }
            
            return redirect()->route('seller.products.create.step', ['step' => 4])
                ->with('success', 'Medien hochgeladen');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Upload fehlgeschlagen: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * SCHRITT 4: Preisgestaltung speichern
     * POST /seller/products/create/step4
     */
    public function storeStep4(ProductStep4Request $request)
    {
        $validated = $request->validated();
        $product = Product::where('id', session('product_draft_id'))
            ->where('seller_id', Auth::id())
            ->firstOrFail();
        
        $product = $this->productService->saveStep4($product, $validated);
        
        return redirect()->route('seller.products.create.step', ['step' => 5])
            ->with('success', 'Preisgestaltung gespeichert');
    }
    
    /**
     * SCHRITT 5: Veröffentlichen
     * POST /seller/products/create/step5
     */
    public function storeStep5(ProductStep5Request $request)
    {
        $validated = $request->validated();
        $product = Product::where('id', session('product_draft_id'))
            ->where('seller_id', Auth::id())
            ->firstOrFail();
        
        $result = $this->productService->saveAndPublish($product, $validated);
        
        if ($result['success']) {
            // Entferne Draft-ID aus Session
            session()->forget('product_draft_id');
            
            return redirect()->route('seller.dashboard')
                ->with('success', $result['message']);
        }
        
        return back()
            ->with('error', $result['message'])
            ->withInput();
    }
    
    // ============================================
    // AJAX ENDPOINTS
    // ============================================
    
    /**
     * AJAX: Auto-Save
     * POST /seller/products/auto-save
     */
    public function autoSave(Request $request)
    {
        try {
            $draftId = session('product_draft_id');
            
            if (!$draftId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kein Draft gefunden',
                ], 400);
            }
            
            $product = Product::where('id', $draftId)
                ->where('seller_id', Auth::id())
                ->firstOrFail();
            
            $success = $this->productService->autoSave($product, $request->all());
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Automatisch gespeichert' : 'Fehler beim Speichern',
                'timestamp' => now()->format('H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * AJAX: Preisempfehlung
     * POST /seller/products/suggest-price
     */
    public function suggestPrice(Request $request)
    {
        try {
            $draftId = session('product_draft_id');
            $product = Product::where('id', $draftId)
                ->where('seller_id', Auth::id())
                ->firstOrFail();
            
            $suggestion = $this->priceService->suggest($product);
            
            return response()->json([
                'success' => true,
                'suggestion' => $suggestion,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler bei Preisempfehlung',
            ], 500);
        }
    }
    
    /**
     * AJAX: Medium löschen
     * DELETE /seller/products/media/{id}
     */
    public function deleteMedia(int $id)
    {
        try {
            $media = ProductImage::findOrFail($id);
            
            // Prüfe Ownership
            if ($media->product->seller_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keine Berechtigung',
                ], 403);
            }
            
            $success = $this->mediaService->deleteMedia($media);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Medium gelöscht' : 'Fehler beim Löschen',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * AJAX: Medien sortieren
     * POST /seller/products/media/reorder
     */
    public function reorderMedia(Request $request)
    {
        try {
            $product = Product::where('id', session('product_draft_id'))
                ->where('seller_id', Auth::id())
                ->firstOrFail();
            
            $orderedIds = $request->input('order', []);
            
            $this->mediaService->reorderMedia($product, $orderedIds);
            
            return response()->json([
                'success' => true,
                'message' => 'Reihenfolge gespeichert',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Sortieren',
            ], 500);
        }
    }
    
    // ============================================
    // HILFSMETHODEN
    // ============================================
    
    /**
     * Berechne Erfolgsquote (Zielpreis erreicht)
     */
    private function calculateSuccessRate(int $sellerId): float
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
        
        return round(($successful / $total) * 100, 1);
    }
    
    /**
     * Berechne Konversionsrate
     */
    private function calculateConversionRate(int $sellerId): float
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
     * Berechne durchschnittliche Tickets pro Verlosung
     */
    private function calculateAvgTicketsPerRaffle(int $sellerId): float
    {
        $avg = Raffle::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->where('status', 'completed')
        ->avg('tickets_sold');
        
        return round($avg ?? 0, 1);
    }
}