<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class PriceSuggestionService
{
    /**
     * Generiere KI-basierte Preisempfehlung für ein Produkt
     * 
     * @param Product $product
     * @return array [suggested_price, min_price, max_price, confidence, based_on, similar_products]
     */
    public function suggest(Product $product): array
    {
        // 1. Finde ähnliche Produkte in gleicher Kategorie
        $similar = $this->findSimilarProducts($product);
        
        // 2. Wenn keine ähnlichen Produkte gefunden: Fallback-Logik
        if ($similar->isEmpty()) {
            return $this->fallbackSuggestion($product);
        }
        
        // 3. Berechne Durchschnittspreis aus erfolgreichen Verlosungen
        $avgPrice = $similar->avg('target_price');
        
        // 4. Anpassung nach Zustand
        $conditionMultiplier = $this->getConditionMultiplier($product->condition);
        $adjustedPrice = $avgPrice * $conditionMultiplier;
        
        // 5. Marken-Bonus (falls bekannte Marke)
        if ($product->brand) {
            $brandBonus = $this->getBrandBonus($product->brand);
            $adjustedPrice *= (1 + $brandBonus);
        }
        
        // 6. Confidence Score berechnen (0-100)
        $confidence = $this->calculateConfidence($similar->count());
        
        // 7. Min/Max Range
        $minPrice = round($adjustedPrice * 0.8, 2);
        $maxPrice = round($adjustedPrice * 1.2, 2);
        
        return [
            'suggested_price' => round($adjustedPrice, 2),
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'confidence' => $confidence,
            'based_on' => $similar->count(),
            'similar_products' => $this->formatSimilarProducts($similar->take(3)),
            'reasoning' => $this->generateReasoning($product, $similar->count(), $confidence),
        ];
    }
    
    /**
     * Finde ähnliche Produkte für Preisanalyse
     */
    private function findSimilarProducts(Product $product): Collection
    {
        $query = Product::query()
            ->where('category_id', $product->category_id)
            ->where('status', '!=', 'draft')
            ->where('id', '!=', $product->id ?? 0);
        
        // Filter nach Marke (wenn vorhanden)
        if ($product->brand) {
            $query->where('brand', $product->brand);
        }
        
        // Nur Produkte mit erfolgreichen Raffles
        $query->whereHas('raffle', function($q) {
            $q->where('target_reached', true)
              ->where('status', 'completed');
        });
        
        // Sortiere nach Datum (neueste zuerst)
        $query->orderBy('created_at', 'desc')
              ->limit(20);
        
        return $query->get();
    }
    
    /**
     * Fallback-Suggestion wenn keine ähnlichen Produkte gefunden
     */
    private function fallbackSuggestion(Product $product): array
    {
        // Basis-Preis: 60% vom UVP oder Standardwert
        $basePrice = $product->retail_price 
            ? $product->retail_price * 0.6 
            : 100;
        
        // Anpassung nach Zustand
        $conditionMultiplier = $this->getConditionMultiplier($product->condition);
        $adjustedPrice = $basePrice * $conditionMultiplier;
        
        return [
            'suggested_price' => round($adjustedPrice, 2),
            'min_price' => round($adjustedPrice * 0.7, 2),
            'max_price' => round($adjustedPrice * 1.3, 2),
            'confidence' => 30, // Niedrige Konfidenz
            'based_on' => 0,
            'similar_products' => [],
            'reasoning' => 'Keine ähnlichen Produkte gefunden. Empfehlung basiert auf UVP und Zustand.',
        ];
    }
    
    /**
     * Zustand-Multiplikator
     */
    private function getConditionMultiplier(string $condition): float
    {
        return match($condition) {
            'new' => 1.0,
            'like_new' => 0.85,
            'good' => 0.70,
            'acceptable' => 0.55,
            default => 0.70,
        };
    }
    
    /**
     * Marken-Bonus (Premium-Marken erzielen höhere Preise)
     */
    private function getBrandBonus(string $brand): float
    {
        $premiumBrands = [
            'Apple' => 0.15,
            'Samsung' => 0.10,
            'Sony' => 0.10,
            'Nike' => 0.12,
            'Dyson' => 0.15,
            'Bose' => 0.10,
        ];
        
        return $premiumBrands[$brand] ?? 0;
    }
    
    /**
     * Berechne Confidence Score (0-100)
     * Je mehr ähnliche Produkte, desto höher die Konfidenz
     */
    private function calculateConfidence(int $productCount): int
    {
        if ($productCount === 0) return 30;
        if ($productCount >= 10) return 95;
        
        // Linear scaling: 30 + (productCount * 6.5)
        return min(95, 30 + ($productCount * 6.5));
    }
    
    /**
     * Formatiere ähnliche Produkte für Ausgabe
     */
    private function formatSimilarProducts(Collection $products): array
    {
        return $products->map(function($product) {
            return [
                'title' => $product->title,
                'price' => $product->target_price,
                'condition' => $product->condition,
                'sold_tickets' => $product->raffle->tickets_sold ?? 0,
            ];
        })->toArray();
    }
    
    /**
     * Generiere Begründung für Preisempfehlung
     */
    private function generateReasoning(Product $product, int $count, int $confidence): string
    {
        $reasons = [];
        
        if ($count > 0) {
            $reasons[] = "Basierend auf {$count} ähnlichen Produkten";
        }
        
        if ($product->brand) {
            $reasons[] = "Marke '{$product->brand}' berücksichtigt";
        }
        
        $conditionText = match($product->condition) {
            'new' => 'Neuwert',
            'like_new' => 'Wie neu',
            'good' => 'Guter Zustand',
            'acceptable' => 'Akzeptabler Zustand',
        };
        $reasons[] = "Zustand: {$conditionText}";
        
        if ($confidence >= 80) {
            $reasons[] = "Hohe Datenbasis";
        } elseif ($confidence < 50) {
            $reasons[] = "Begrenzte Datenbasis";
        }
        
        return implode(' • ', $reasons);
    }
}