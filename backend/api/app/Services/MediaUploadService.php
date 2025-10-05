<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Media;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class MediaUploadService
{
    /**
     * Upload und verarbeite eine Media-Datei
     */
    public function uploadMedia(
        UploadedFile $file,
        Product $product,
        int $position = 0,
        bool $isPrimary = false
    ): Media {
        // Dateiinformationen
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $mediaType = $this->getMediaType($mimeType);
        
        // Generiere eindeutigen Dateinamen
        $filename = Str::uuid() . '.' . $extension;
        
        // Speicherpfad
        $directory = 'products/' . $product->id . '/' . $mediaType . 's';
        
        // Datei speichern
        $path = $file->storeAs($directory, $filename, 'public');
        $fullPath = storage_path('app/public/' . $path);
        
        // Thumbnail erstellen
        $thumbnailPath = $this->createThumbnail($fullPath, $mediaType);
        
        // Media-Datenbank-Eintrag erstellen
        $media = Media::create([
            'product_id' => $product->id,
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'media_type' => $mediaType,
            'position' => $position,
            'is_primary' => $isPrimary,
        ]);
        
        return $media;
    }
    
    /**
     * Bestimme Media-Typ aus MIME-Type
     */
    private function getMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        return 'file';
    }
    
    /**
     * Erstelle Thumbnail für Bild oder Video
     */
    private function createThumbnail(string $filePath, string $mediaType): ?string
    {
        // Thumbnail-Verzeichnis
        $pathInfo = pathinfo($filePath);
        $thumbnailDir = $pathInfo['dirname'] . '/thumbnails';
        
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        // Thumbnail-Dateiname
        $thumbnailFilename = $pathInfo['filename'] . '_thumb.jpg';
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;
        
        try {
            if ($mediaType === 'image') {
                // Bild-Thumbnail mit Intervention/Image v3
                $manager = new ImageManager(new Driver());
                $img = $manager->read($filePath);
                
                // Cover erstellt ein Thumbnail mit exakten Dimensionen (beschneidet wenn nötig)
                $img->cover(300, 300);
                
                // Als JPEG mit 80% Qualität speichern
                $img->toJpeg(80)->save($thumbnailPath);
                
            } else {
                // Video-Thumbnail mit FFmpeg (oder Fallback)
                $thumbnailPath = $this->createVideoThumbnail($filePath, $thumbnailPath);
            }
            
            // Relativer Pfad für URL
            $relativePath = str_replace(storage_path('app/public/'), '', $thumbnailPath);
            return $relativePath;
            
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Erstelle Video-Thumbnail mit FFmpeg
     */
    private function createVideoThumbnail(string $videoPath, string $thumbnailPath): string
    {
        try {
            // Versuche FFmpeg zu verwenden
            if (class_exists('FFMpeg\FFMpeg')) {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries'  => config('media.ffmpeg_path', '/usr/bin/ffmpeg'),
                    'ffprobe.binaries' => config('media.ffprobe_path', '/usr/bin/ffprobe'),
                ]);
                
                $video = $ffmpeg->open($videoPath);
                $frame = $video->frame(TimeCode::fromSeconds(1));
                $frame->save($thumbnailPath);
                
                // Thumbnail mit Intervention/Image auf richtige Größe bringen
                $manager = new ImageManager(new Driver());
                $img = $manager->read($thumbnailPath);
                $img->cover(300, 300);
                $img->toJpeg(80)->save($thumbnailPath);
                
                return $thumbnailPath;
            }
        } catch (\Exception $e) {
            \Log::warning('FFmpeg thumbnail creation failed: ' . $e->getMessage());
        }
        
        // Fallback: Verwende Standard-Video-Icon
        return $this->createVideoPlaceholder($thumbnailPath);
    }
    
    /**
     * Erstelle Platzhalter-Thumbnail für Videos
     */
    private function createVideoPlaceholder(string $thumbnailPath): string
    {
        $manager = new ImageManager(new Driver());
        
        // Erstelle ein einfaches graues Bild als Platzhalter
        $img = $manager->create(300, 300);
        
        // Füge Text hinzu (optional, wenn du möchtest)
        // $img->text('VIDEO', 150, 150, function($font) {
        //     $font->file(public_path('fonts/arial.ttf'));
        //     $font->size(24);
        //     $font->color('#ffffff');
        //     $font->align('center');
        //     $font->valign('middle');
        // });
        
        $img->toJpeg(80)->save($thumbnailPath);
        
        return $thumbnailPath;
    }
    
    /**
     * Lösche Media und zugehörige Dateien
     */
    public function deleteMedia(Media $media): bool
    {
        try {
            // Lösche physische Dateien
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            
            if ($media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }
            
            // Lösche Datenbank-Eintrag
            $media->delete();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Media deletion failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Aktualisiere Position von Media-Dateien
     */
    public function updatePositions(array $mediaIds): void
    {
        foreach ($mediaIds as $position => $mediaId) {
            Media::where('id', $mediaId)->update(['position' => $position]);
        }
    }
    
    /**
     * Setze primäres Bild
     */
    public function setPrimary(Media $media): void
    {
        // Setze alle anderen Medien des Produkts auf nicht-primär
        Media::where('product_id', $media->product_id)
            ->update(['is_primary' => false]);
        
        // Setze das gewählte Medium auf primär
        $media->update(['is_primary' => true]);
    }
}