<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'description',
        'brand',
        'model',
        'condition',
        'retail_price',
        'target_price',
        'decision_type',
        'status',
        'slug',
        'images',
        'view_count',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'images' => 'array',
        'view_count' => 'integer',
    ];

    /**
     * Get the seller that owns the product
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the raffle for the product
     */
    public function raffle()
    {
        return $this->hasOne(Raffle::class);
    }

    /**
     * Get the product images
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Beziehung zu Media
     */
    public function media()
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }

    /**
     * Beziehung zum primären Bild
     */
    public function primaryMedia()
    {
        return $this->hasOne(Media::class)->where('is_primary', true);
    }

    /**
     * Nur Bilder
     */
    public function images()
    {
        return $this->hasMany(Media::class)->where('media_type', 'image')->orderBy('position');
    }

    /**
     * Nur Videos
     */
    public function videos()
    {
        return $this->hasMany(Media::class)->where('media_type', 'video')->orderBy('position');
    }

    /**
     * Accessor für primäres Bild URL
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primaryMedia = $this->primaryMedia;
        return $primaryMedia ? $primaryMedia->url : null;
    }
}