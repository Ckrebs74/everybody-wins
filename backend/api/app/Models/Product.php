<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'view_count'
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'view_count' => 'integer',
        'images' => 'array'  // Für die alte JSON-Spalte, falls noch verwendet
    ];

    /**
     * Get the images for the product.
     * WICHTIG: Diese Relationship lädt die Bilder aus der product_images Tabelle
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image for the product.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the seller (user) who owns the product.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the category of the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the raffle for the product.
     */
    public function raffle(): HasOne
    {
        return $this->hasOne(Raffle::class);
    }

    /**
     * Get remaining tickets
     */
    public function getRemainingTickets()
    {
        if (!$this->raffle) {
            return 0;
        }
        return $this->raffle->total_target - $this->raffle->tickets_sold;
    }
}