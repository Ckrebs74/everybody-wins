<?php

// =====================================================
// MODEL 4: app/Models/Product.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'target_price',
        'ticket_price',
        'end_date',
        'status',
        'decision_type',
        'category_id',
        'condition',
        'brand',
        'model_number',
        'shipping_cost',
        'shipping_info'
    ];

    protected $casts = [
        'end_date' => 'datetime',
        'target_price' => 'decimal:2',
        'ticket_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2'
    ];

    protected $appends = ['primary_image', 'tickets_sold', 'progress_percentage'];

    /**
     * Get all images for this product
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
     * Get primary image URL attribute
     */
    public function getPrimaryImageAttribute()
    {
        $primary = $this->primaryImage()->first();
        if ($primary) {
            return $primary->full_url;
        }
        
        // Fallback to first image
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->full_url;
        }
        
        // Default placeholder
        return '/images/no-product-image.png';
    }

    /**
     * Get primary thumbnail URL
     */
    public function getPrimaryThumbnailAttribute()
    {
        $primary = $this->primaryImage()->first();
        if ($primary) {
            return $primary->thumbnail_url;
        }
        
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->thumbnail_url;
        }
        
        return '/images/no-product-image-thumb.png';
    }

    /**
     * Other existing relationships...
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getTicketsSoldAttribute()
    {
        return $this->tickets()->count();
    }

    public function getProgressPercentageAttribute()
    {
        $progress = ($this->tickets_sold * $this->ticket_price) / $this->target_price * 100;
        return min(100, round($progress, 2));
    }
}