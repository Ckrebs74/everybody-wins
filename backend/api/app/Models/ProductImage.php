<?php
// =====================================================
// FILE: app/Models/ProductImage.php
// =====================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'alt_text',
        'sort_order',
        'is_primary'
    ];

    protected $appends = ['full_url', 'thumbnail_url'];

    /**
     * Get the full URL for the image
     */
    public function getFullUrlAttribute()
    {
        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }
        return $this->full_url;
    }

    /**
     * Relationship to product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Delete physical files when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            Storage::disk('public')->delete($image->image_path);
            if ($image->thumbnail_path) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
        });
    }
}