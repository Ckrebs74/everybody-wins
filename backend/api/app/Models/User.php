<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'wallet_balance',
        'total_deposited',
        'total_spent',
        'total_withdrawn',
        'stripe_customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'total_deposited' => 'decimal:2',
            'total_spent' => 'decimal:2',
            'total_withdrawn' => 'decimal:2',
        ];
    }

    /**
     * Alle Produkte, die dieser User verkauft
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Alle Tickets, die dieser User gekauft hat
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Alle Transaktionen dieses Users
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Alle Verlosungen, die dieser User gewonnen hat
     */
    public function wonRaffles(): HasMany
    {
        return $this->hasMany(Raffle::class, 'winner_id');
    }

    /**
     * Spending Limits dieses Users
     */
    public function spendingLimits(): HasMany
    {
        return $this->hasMany(SpendingLimit::class);
    }

    /**
     * Prüft ob User Verkäufer-Rechte hat
     */
    public function isSeller(): bool
    {
        return in_array($this->role, ['seller', 'both', 'admin']);
    }

    /**
     * Prüft ob User Admin ist
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Prüft ob User Käufer-Rechte hat
     */
    public function isBuyer(): bool
    {
        return in_array($this->role, ['buyer', 'both', 'admin']);
    }

    /**
     * Gibt die Rolle als lesbaren Text zurück
     */
    public function getRoleText(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'seller' => 'Verkäufer',
            'buyer' => 'Käufer',
            'both' => 'Käufer & Verkäufer',
            default => 'Unbekannt'
        };
    }

    /**
     * Gibt ein HTML-Badge für die Rolle zurück
     */
    public function getRoleBadge(): string
    {
        return match($this->role) {
            'admin' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Admin</span>',
            'seller' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Verkäufer</span>',
            'buyer' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Käufer</span>',
            'both' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Käufer & Verkäufer</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unbekannt</span>'
        };
    }

    /**
     * Erhöht das Wallet-Guthaben
     */
    public function addFunds(float $amount): void
    {
        $this->increment('wallet_balance', $amount);
        $this->increment('total_deposited', $amount);
    }

    /**
     * Verringert das Wallet-Guthaben
     */
    public function deductFunds(float $amount): bool
    {
        if ($this->wallet_balance < $amount) {
            return false;
        }

        $this->decrement('wallet_balance', $amount);
        $this->increment('total_spent', $amount);
        return true;
    }

    /**
     * Prüft ob User genug Guthaben hat
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }
}