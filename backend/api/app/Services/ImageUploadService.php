
<?php
// =====================================================
// FILE: app/Services/ImageUploadService.php
// =====================================================
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class ImageUploadService
{
    protected $maxWidth = 1200;
    protected $maxHeight = 1200;
    protected $thumbnailWidth = 300;
    protected $thumbnailHeight = 300;
    protected $quality = 85;

    /**
     * Upload and process product image
     */
    public function uploadProductImage(UploadedFile $file, int $productId): array
    {
        // Generate unique filename
        $filename = $this->generateFilename($file);
        
        // Define paths
        $imagePath = "products/{$productId}/{$filename}";
        $thumbnailPath = "products/{$productId}/thumb_{$filename}";
        
        // Process main image
        $image = Image::make($file);
        
        // Resize if needed (maintain aspect ratio)
        if ($image->width() > $this->maxWidth || $image->height() > $this->maxHeight) {
            $image->resize($this->maxWidth, $this->maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Save main image
        Storage::disk('public')->put(
            $imagePath,
            $image->encode(null, $this->quality)->__toString()
        );
        
        // Create thumbnail
        $thumbnail = Image::make($file);
        $thumbnail->fit($this->thumbnailWidth, $this->thumbnailHeight);
        
        // Save thumbnail
        Storage::disk('public')->put(
            $thumbnailPath,
            $thumbnail->encode(null, $this->quality)->__toString()
        );
        
        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Upload multiple images
     */
    public function uploadMultiple(array $files, int $productId): array
    {
        $uploadedImages = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedImages[] = $this->uploadProductImage($file, $productId);
            }
        }
        
        return $uploadedImages;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::random(40) . '.' . $extension;
    }

    /**
     * Delete product images
     */
    public function deleteProductImages(int $productId): void
    {
        Storage::disk('public')->deleteDirectory("products/{$productId}");
    }
}