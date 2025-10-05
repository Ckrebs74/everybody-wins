<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'media_type',
        'image_path',
        'thumbnail_path',
        'alt_text',
        'sort_order',
        'is_primary',
        'file_size',
        'duration',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Beziehung zum Produkt
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Prüfe ob es ein Bild ist
     */
    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    /**
     * Prüfe ob es ein Video ist
     */
    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    /**
     * Formatierte Dateigröße
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        if ($this->file_size < 1024) {
            return $this->file_size . ' KB';
        }

        return round($this->file_size / 1024, 2) . ' MB';
    }

    /**
     * Formatierte Video-Dauer
     */
    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Scope: Nur Bilder
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope: Nur Videos
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    /**
     * Scope: Primary Media
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: Sortiert nach sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}