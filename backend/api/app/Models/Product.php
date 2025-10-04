<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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
        'view_count'
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'images' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // WICHTIG: Products haben eine Raffle, nicht direkt Tickets!
    public function raffle()
    {
        return $this->hasOne(Raffle::class);
    }

    // Tickets über Raffle erreichen
    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, Raffle::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // Accessors
    public function getPrimaryImageUrlAttribute()
    {
        $primary = $this->primaryImage()->first();
        if ($primary) {
            return $primary->image_path;
        }
        
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_path;
        }
        
        return 'https://via.placeholder.com/300x300/FFD700/333333?text=' . urlencode($this->title);
    }

    public function getTicketsSoldAttribute()
    {
        // Tickets über die Raffle zählen
        if ($this->raffle) {
            return $this->raffle->tickets_sold;
        }
        return 0;
    }

    public function getProgressPercentageAttribute()
    {
        if (!$this->target_price || $this->target_price <= 0) return 0;
        
        // Nutze die Raffle-Daten
        if ($this->raffle) {
            $progress = ($this->raffle->total_revenue / $this->raffle->total_target) * 100;
            return min(100, round($progress, 2));
        }
        return 0;
    }

    public function getTicketPriceAttribute()
    {
        // Standard-Ticketpreis ist 1€ (oder aus Raffle)
        return 1.00;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function canBePurchased()
    {
        return $this->isActive() && $this->raffle && $this->raffle->status === 'active';
    }

    public function getRemainingTickets()
    {
        if (!$this->raffle || !$this->target_price) return 0;
        
        $totalNeeded = $this->raffle->total_target;
        $totalSold = $this->raffle->total_revenue;
        
        return max(0, $totalNeeded - $totalSold);
    }
}