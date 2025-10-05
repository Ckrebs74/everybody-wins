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

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function raffle()
    {
        return $this->hasOne(Raffle::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }
}