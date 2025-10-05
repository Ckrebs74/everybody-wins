<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Raffle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductCreationService
{
    /**
     * Erstelle oder hole Draft-Produkt
     */
    public function getOrCreateDraft(int $sellerId, ?int $draftId = null): Product
    {
        if ($draftId) {
            $product = Product::where('id', $draftId)
                ->where('seller_id', $sellerId)
                ->where('status', 'draft')
                ->first();
                
            if ($product) {
                return $product;
            }
        }
        
        // Erstelle neuen Draft
        return Product::create([
            'seller_id' => $sellerId,
            'status' => 'draft',
            'title' => 'Entwurf',
            'description' => '',
            'target_price' => 0,
            'slug' => $this->generateUniqueSlug('entwurf'),
        ]);
    }
    
    /**
     * Speichere Schritt 1: Kategorie & Typ
     */
    public function saveStep1(Product $product, array $data): Product
    {
        $product->update([
            'category_id' => $data['category_id'],
            'condition' => $data['condition'] ?? 'new',
        ]);
        
        return $product->fresh();
    }
    
    /**
     * Speichere Schritt 2: Produktdetails
     */
    public function saveStep2(Product $product, array $data): Product
    {
        // Sanitize Inputs
        $sanitized = [
            'title' => strip_tags($data['title']),
            'description' => strip_tags($data['description'], '<br><p><strong><em><ul><li>'),
            'brand' => isset($data['brand']) ? htmlspecialchars($data['brand'], ENT_QUOTES, 'UTF-8') : null,
            'model' => isset($data['model']) ? htmlspecialchars($data['model'], ENT_QUOTES, 'UTF-8') : null,
            'condition' => $data['condition'],
            'retail_price' => $data['retail_price'] ?? null,
        ];
        
        // Generiere SEO-Slug
        if ($product->title !== $sanitized['title']) {
            $sanitized['slug'] = $this->generateUniqueSlug($sanitized['title']);
        }
        
        $product->update($sanitized);
        
        return $product->fresh();
    }
    
    /**
     * Auto-Save (AJAX) - Speichere partial data
     */
    public function autoSave(Product $product, array $data): bool
    {
        try {
            // Nur erlaubte Felder aktualisieren
            $allowed = ['title', 'description', 'brand', 'model', 'condition', 'retail_price', 'target_price', 'decision_type'];
            $update = array_intersect_key($data, array_flip($allowed));
            
            // Sanitize
            if (isset($update['title'])) {
                $update['title'] = strip_tags($update['title']);
            }
            if (isset($update['description'])) {
                $update['description'] = strip_tags($update['description'], '<br><p><strong><em><ul><li>');
            }
            
            $product->update($update);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Speichere Schritt 4: Preisgestaltung
     */
    public function saveStep4(Product $product, array $data): Product
    {
        // Plattformprovision 30% vom Endpreis
        // Endpreis = Zielpreis + Provision
        // Also: Zielpreis = X, Endpreis = X * 1.42857 (damit 30% von Endpreis = 0.3 * Endpreis)
        // Berechnung: Zielpreis / 0.7 = Endpreis
        
        $targetPrice = $data['target_price'];
        $totalTarget = round($targetPrice / 0.7, 2); // Endpreis
        $platformFee = round($totalTarget * 0.3, 2); // 30% vom Endpreis
        
        $product->update([
            'target_price' => $targetPrice,
            'decision_type' => $data['decision_type'],
        ]);
        
        return $product->fresh();
    }
    
    /**
     * Speichere Schritt 5 & Veröffentliche
     */
    public function saveAndPublish(Product $product, array $data): array
    {
        DB::beginTransaction();
        
        try {
            // 1. Prüfe ob Produkt vollständig ist
            $this->validateComplete($product);
            
            // 2. Berechne Raffle-Daten
            $startsAt = $data['starts_at'] ?? now();
            $duration = (int)$data['duration_days'];
            $endsAt = $startsAt instanceof \Carbon\Carbon 
                ? $startsAt->copy()->addDays($duration)
                : now()->addDays($duration);
            
            // 3. Berechne Preise
            $targetPrice = $product->target_price;
            $totalTarget = round($targetPrice / 0.7, 2);
            $platformFee = round($totalTarget * 0.3, 2);
            
            // 4. Update Product Status
            $status = ($startsAt > now()) ? 'scheduled' : 'active';
            $product->update(['status' => $status]);
            
            // 5. Erstelle Raffle
            $raffle = Raffle::create([
                'product_id' => $product->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'target_price' => $targetPrice,
                'platform_fee' => $platformFee,
                'total_target' => $totalTarget,
                'status' => $status,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'product' => $product,
                'raffle' => $raffle,
                'message' => $status === 'scheduled' 
                    ? 'Produkt wurde geplant und wird zum festgelegten Zeitpunkt veröffentlicht.'
                    : 'Produkt wurde erfolgreich veröffentlicht!',
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Fehler beim Veröffentlichen: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Validiere ob Produkt vollständig ist
     */
    private function validateComplete(Product $product): void
    {
        $errors = [];
        
        if (empty($product->title) || strlen($product->title) < 10) {
            $errors[] = 'Titel muss mindestens 10 Zeichen lang sein';
        }
        
        if (empty($product->description) || strlen($product->description) < 50) {
            $errors[] = 'Beschreibung muss mindestens 50 Zeichen lang sein';
        }
        
        if ($product->target_price <= 0) {
            $errors[] = 'Zielpreis muss größer als 0 sein';
        }
        
        if (empty($product->decision_type)) {
            $errors[] = 'Entscheidung (Behalten/Abgeben) muss getroffen werden';
        }
        
        // Min. 1 Medium erforderlich
        $mediaCount = $product->images()->count();
        if ($mediaCount === 0) {
            $errors[] = 'Mindestens 1 Bild oder Video ist erforderlich';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
    }
    
    /**
     * Generiere einzigartigen Slug
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug . '-' . substr(md5(uniqid()), 0, 13);
        
        // Prüfe auf Duplikate
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . substr(md5(uniqid()), 0, 13);
        }
        
        return $slug;
    }
    
    /**
     * Lösche Draft (nur wenn kein Raffle existiert)
     */
    public function deleteDraft(Product $product): bool
    {
        // Prüfe ob Raffle existiert
        if ($product->raffle()->exists()) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            // Lösche Medien
            foreach ($product->images as $media) {
                app(MediaUploadService::class)->deleteMedia($media);
            }
            
            // Lösche Produkt
            $product->delete();
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}