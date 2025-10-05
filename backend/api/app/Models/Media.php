<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_path',
        'thumbnail_path',
        'file_name',
        'file_size',
        'mime_type',
        'media_type',
        'position',
        'is_primary',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'position' => 'integer',
        'is_primary' => 'boolean',
    ];

    /**
     * Beziehung zum Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor für vollständige URL der Datei
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Accessor für vollständige URL des Thumbnails
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        return Storage::url($this->thumbnail_path);
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
     * Scope: Nach Position sortiert
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Scope: Nur primäre Medien
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}