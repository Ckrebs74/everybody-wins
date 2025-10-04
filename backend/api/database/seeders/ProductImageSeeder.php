<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        
        if ($products->count() === 0) {
            $this->command->error('Keine Produkte gefunden! Führe erst den DemoSeeder aus.');
            return;
        }
        
        $this->command->info('Erstelle Demo-Bilder für ' . $products->count() . ' Produkte...');

        foreach ($products as $product) {
            // Lösche alte Bilder falls vorhanden
            $product->images()->delete();
            
            // Generiere 3-5 Placeholder-Bilder pro Produkt
            $imageCount = rand(3, 5);
            
            // Produktspezifische Farben basierend auf Kategorie
            $categoryColors = [
                'Elektronik' => ['4A90E2', '7B68EE', '6A5ACD', '4169E1'],
                'Gaming' => ['FF6B6B', 'DC143C', 'FF4500', 'FF1493'],
                'Mode' => ['4ECDC4', '45B7D1', '40E0D0', '48D1CC'],
                'Haushalt' => ['95E1D3', 'A8E6CF', '90EE90', '98FB98'],
                'Sport' => ['FFA500', 'FF8C00', 'FFB347', 'FFAC1C'],
                'Beauty' => ['FFB6C1', 'FFC0CB', 'FF69B4', 'FF1493']
            ];
            
            // Wähle Farben basierend auf Kategorie oder nutze Default
            $colors = $categoryColors[$product->category->name ?? 'default'] ?? ['FFD700', 'FFA500', 'FF6347', 'FF4500'];
            
            for ($i = 0; $i < $imageCount; $i++) {
                $color = $colors[array_rand($colors)];
                
                // Erstelle verschiedene Bildtexte
                $imageTexts = [
                    'Hauptbild',
                    'Seitenansicht',
                    'Detailansicht', 
                    'Verpackung',
                    'Zubehör'
                ];
                
                $imageText = $imageTexts[$i] ?? 'Bild ' . ($i + 1);
                
                // Placeholder.com URLs (externe URLs für Demo)
                $imageUrl = "https://via.placeholder.com/800x800/{$color}/FFFFFF?text=" . urlencode($product->title . ' - ' . $imageText);
                $thumbnailUrl = "https://via.placeholder.com/300x300/{$color}/FFFFFF?text=" . urlencode($imageText);
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imageUrl,
                    'thumbnail_path' => $thumbnailUrl,
                    'alt_text' => $product->title . ' - ' . $imageText,
                    'sort_order' => $i,
                    'is_primary' => $i === 0 // Erstes Bild ist Hauptbild
                ]);
            }
            
            $this->command->info("✓ {$imageCount} Bilder für '{$product->title}' erstellt");
        }
        
        $totalImages = ProductImage::count();
        $this->command->info("✅ Fertig! {$totalImages} Demo-Bilder wurden erstellt!");
        $this->command->info("🎯 Öffne http://localhost:8080/products um die Bilder zu sehen!");
    }
}