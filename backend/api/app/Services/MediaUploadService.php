<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;

class MediaUploadService
{
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const ALLOWED_VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime'];
    private const MAX_IMAGE_SIZE = 5120; // 5MB in KB
    private const MAX_VIDEO_SIZE = 51200; // 50MB in KB
    
    /**
     * Upload und verarbeite ein Medium (Bild oder Video)
     * 
     * @param UploadedFile $file
     * @param Product $product
     * @param int $sortOrder
     * @param bool $isPrimary
     * @return ProductImage
     * @throws Exception
     */
    public function uploadMedia(
        UploadedFile $file, 
        Product $product, 
        int $sortOrder = 0, 
        bool $isPrimary = false
    ): ProductImage {
        // 1. Validiere MIME-Type
        $mimeType = $file->getMimeType();
        $mediaType = $this->determineMediaType($mimeType);
        
        if (!$mediaType) {
            throw new Exception('Ungültiger Dateityp');
        }
        
        // 2. Validiere Dateigröße
        $this->validateFileSize($file, $mediaType);
        
        // 3. Generiere sicheren Dateinamen
        $filename = $this->generateSecureFilename($file);
        $path = "products/{$product->seller_id}";
        
        // 4. Speichere Datei
        $storedPath = $file->storeAs($path, $filename, 'public');
        $fullPath = Storage::disk('public')->path($storedPath);
        
        // 5. Erstelle Thumbnail
        $thumbnailPath = $this->createThumbnail($fullPath, $mediaType);
        
        // 6. Hole zusätzliche Metadaten
        $metadata = $this->extractMetadata($fullPath, $mediaType);
        
        // 7. Wenn Primary-Bild, entferne Primary-Flag von anderen
        if ($isPrimary) {
            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);
        }
        
        // 8. Erstelle Datenbankeintrag
        return ProductImage::create([
            'product_id' => $product->id,
            'media_type' => $mediaType,
            'image_path' => Storage::disk('public')->url($storedPath),
            'thumbnail_path' => $thumbnailPath,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary,
            'file_size' => $metadata['file_size'],
            'duration' => $metadata['duration'] ?? null,
            'alt_text' => $this->generateAltText($product, $mediaType),
        ]);
    }
    
    /**
     * Bestimme Media-Typ anhand MIME-Type
     */
    private function determineMediaType(string $mimeType): ?string
    {
        if (in_array($mimeType, self::ALLOWED_IMAGE_MIMES)) {
            return 'image';
        }
        
        if (in_array($mimeType, self::ALLOWED_VIDEO_MIMES)) {
            return 'video';
        }
        
        return null;
    }
    
    /**
     * Validiere Dateigröße
     */
    private function validateFileSize(UploadedFile $file, string $mediaType): void
    {
        $fileSizeKB = $file->getSize() / 1024;
        
        if ($mediaType === 'image' && $fileSizeKB > self::MAX_IMAGE_SIZE) {
            throw new Exception('Bild ist zu groß (max. 5MB)');
        }
        
        if ($mediaType === 'video' && $fileSizeKB > self::MAX_VIDEO_SIZE) {
            throw new Exception('Video ist zu groß (max. 50MB)');
        }
    }
    
    /**
     * Generiere sicheren, einzigartigen Dateinamen
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }
    
    /**
     * Erstelle Thumbnail für Bild oder Video
     */
    private function createThumbnail(string $filePath, string $mediaType): string
    {
        $thumbnailFilename = pathinfo($filePath, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbnailDir = pathinfo($filePath, PATHINFO_DIRNAME);
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;
        
        try {
            if ($mediaType === 'image') {
                // Bild-Thumbnail mit Intervention/Image
                $img = Image::make($filePath);
                $img->fit(300, 300, function ($constraint) {
                    $constraint->upsize();
                });
                $img->encode('jpg', 80);
                $img->save($thumbnailPath);
            } else {
                // Video-Thumbnail mit FFmpeg (oder Fallback)
                $thumbnailPath = $this->createVideoThumbnail($filePath, $thumbnailPath);
            }
            
            // Relativer Pfad für URL
            $relativePath = str_replace(Storage::disk('public')->path(''), '', $thumbnailPath);
            return Storage::disk('public')->url($relativePath);
            
        } catch (Exception $e) {
            // Fallback: Platzhalter-Thumbnail
            return '/images/placeholder-' . $mediaType . '.jpg';
        }
    }
    
    /**
     * Erstelle Video-Thumbnail (mit FFmpeg falls verfügbar)
     */
    private function createVideoThumbnail(string $videoPath, string $thumbnailPath): string
    {
        // Prüfe ob FFmpeg verfügbar ist
        if (class_exists('FFMpeg\FFMpeg')) {
            try {
                $ffmpeg = \FFMpeg\FFMpeg::create();
                $video = $ffmpeg->open($videoPath);
                
                // Frame bei 1 Sekunde extrahieren
                $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                $frame->save($thumbnailPath);
                
                return $thumbnailPath;
            } catch (Exception $e) {
                // Fallback bei Fehler
            }
        }
        
        // Fallback: Kopiere ein Standard-Video-Thumbnail
        $placeholderPath = public_path('images/video-placeholder.jpg');
        if (file_exists($placeholderPath)) {
            copy($placeholderPath, $thumbnailPath);
            return $thumbnailPath;
        }
        
        return $thumbnailPath;
    }
    
    /**
     * Extrahiere Metadaten (Dateigröße, Video-Duration)
     */
    private function extractMetadata(string $filePath, string $mediaType): array
    {
        $metadata = [
            'file_size' => round(filesize($filePath) / 1024, 2), // KB
            'duration' => null,
        ];
        
        // Video-Duration extrahieren (falls FFmpeg verfügbar)
        if ($mediaType === 'video' && class_exists('FFMpeg\FFMpeg')) {
            try {
                $ffprobe = \FFMpeg\FFProbe::create();
                $duration = $ffprobe
                    ->format($filePath)
                    ->get('duration');
                
                $metadata['duration'] = round($duration);
            } catch (Exception $e) {
                // Duration konnte nicht ermittelt werden
            }
        }
        
        return $metadata;
    }
    
    /**
     * Generiere Alt-Text für Accessibility
     */
    private function generateAltText(Product $product, string $mediaType): string
    {
        $type = $mediaType === 'video' ? 'Video' : 'Bild';
        return "{$product->title} - {$type}";
    }
    
    /**
     * Lösche ein Medium und seine Dateien
     */
    public function deleteMedia(ProductImage $media): bool
    {
        try {
            // Lösche Dateien vom Storage
            $imagePath = str_replace(Storage::disk('public')->url(''), '', $media->image_path);
            $thumbnailPath = str_replace(Storage::disk('public')->url(''), '', $media->thumbnail_path);
            
            Storage::disk('public')->delete($imagePath);
            Storage::disk('public')->delete($thumbnailPath);
            
            // Lösche Datenbankeintrag
            $media->delete();
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Sortiere Medien neu
     */
    public function reorderMedia(Product $product, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ProductImage::where('id', $id)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }
    }
}