<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class MediaUploadService
{
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const ALLOWED_VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime'];
    private const MAX_IMAGE_SIZE = 5120; // 5MB in KB
    private const MAX_VIDEO_SIZE = 51200; // 50MB in KB
    
    public function uploadMedia(
        UploadedFile $file, 
        Product $product, 
        int $sortOrder = 0, 
        bool $isPrimary = false
    ): ProductImage {
        $mimeType = $file->getMimeType();
        $mediaType = $this->determineMediaType($mimeType);
        
        if (!$mediaType) {
            throw new Exception('Ungültiger Dateityp');
        }
        
        $this->validateFileSize($file, $mediaType);
        
        $filename = $this->generateSecureFilename($file);
        $path = "products/{$product->seller_id}";
        
        $storedPath = $file->storeAs($path, $filename, 'public');
        $fullPath = Storage::disk('public')->path($storedPath);
        
        $thumbnailPath = $this->createThumbnail($fullPath, $mediaType);
        
        $metadata = $this->extractMetadata($fullPath, $mediaType);
        
        if ($isPrimary) {
            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);
        }
        
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
    
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }
    
    private function createThumbnail(string $filePath, string $mediaType): string
    {
        $thumbnailFilename = pathinfo($filePath, PATHINFO_FILENAME) . '_thumb.jpg';
        $thumbnailDir = pathinfo($filePath, PATHINFO_DIRNAME);
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;
        
        try {
            if ($mediaType === 'image') {
                // Bild-Thumbnail mit Intervention/Image v3
                $manager = new ImageManager(new Driver());
                $img = $manager->read($filePath);
                $img->cover(300, 300);
                $img->toJpeg(80)->save($thumbnailPath);
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
    
    private function createVideoThumbnail(string $videoPath, string $thumbnailPath): string
    {
        if (class_exists('FFMpeg\FFMpeg')) {
            try {
                $ffmpeg = \FFMpeg\FFMpeg::create();
                $video = $ffmpeg->open($videoPath);
                
                $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                $frame->save($thumbnailPath);
                
                return $thumbnailPath;
            } catch (Exception $e) {
                // Fallback bei Fehler
            }
        }
        
        $placeholderPath = public_path('images/video-placeholder.jpg');
        if (file_exists($placeholderPath)) {
            copy($placeholderPath, $thumbnailPath);
            return $thumbnailPath;
        }
        
        return $thumbnailPath;
    }
    
    private function extractMetadata(string $filePath, string $mediaType): array
    {
        $metadata = [
            'file_size' => round(filesize($filePath) / 1024, 2), // KB
            'duration' => null,
        ];
        
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
    
    private function generateAltText(Product $product, string $mediaType): string
    {
        $type = $mediaType === 'video' ? 'Video' : 'Bild';
        return "{$product->title} - {$type}";
    }
    
    public function deleteMedia(ProductImage $media): bool
    {
        try {
            $imagePath = str_replace(Storage::disk('public')->url(''), '', $media->image_path);
            $thumbnailPath = str_replace(Storage::disk('public')->url(''), '', $media->thumbnail_path);
            
            Storage::disk('public')->delete($imagePath);
            Storage::disk('public')->delete($thumbnailPath);
            
            $media->delete();
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function reorderMedia(Product $product, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ProductImage::where('id', $id)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }
    }
}