// =====================================================
// MODEL 4: app/Models/Product.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'category_id', 'title', 'description',
        'brand', 'model', 'condition', 'retail_price', 'target_price',
        'decision_type', 'status', 'slug', 'images'
    ];

    protected $casts = [
        'images' => 'array',
        'retail_price' => 'decimal:2',
        'target_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title) . '-' . uniqid();
            }
        });
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function raffles()
    {
        return $this->hasMany(Raffle::class);
    }

    public function activeRaffle()
    {
        return $this->hasOne(Raffle::class)->where('status', 'active');
    }
}