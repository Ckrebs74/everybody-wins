<?php
// =====================================================
// MODEL 1: app/Models/User.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email', 'username', 'password', 'role',
        'first_name', 'last_name', 'phone', 'birth_date',
        'street', 'city', 'postal_code', 'country_code',
        'kyc_status', 'age_verified', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'birth_date' => 'date',
        'age_verified' => 'boolean',
        'password' => 'hashed',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function spendingLimits()
    {
        return $this->hasMany(SpendingLimit::class);
    }

    public function isSeller(): bool
    {
        return in_array($this->role, ['seller', 'both', 'admin']);
    }

    public function isBuyer(): bool
    {
        return in_array($this->role, ['buyer', 'both', 'admin']);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}