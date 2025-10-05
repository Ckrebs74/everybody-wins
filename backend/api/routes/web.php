<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaffleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    $activeRaffles = \App\Models\Raffle::with([
        'product' => function($query) {
            $query->with('images');
        }
    ])
        ->where('status', 'active')
        ->limit(6)
        ->get();
    
    return view('welcome', compact('activeRaffles'));
})->name('home');

// Auth Routes (Gast)
Route::middleware('guest')->group(function () {
    Route::get('/register', fn() => view('auth.register'))->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Auth Routes (Eingeloggt)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Wallet
    Route::middleware('auth')->group(function () {
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
        Route::get('/wallet/deposit/success', [WalletController::class, 'depositSuccess'])->name('wallet.deposit.success');
        Route::get('/wallet/withdraw', [WalletController::class, 'showWithdraw'])->name('wallet.withdraw');
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw.post');
    });
    
    // Tickets
    Route::post('/raffles/{raffle}/buy', [TicketController::class, 'purchase'])->name('tickets.purchase');
    Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.index');
});

// Public Raffle Routes - WICHTIG: Slug statt ID!
Route::get('/raffles', [RaffleController::class, 'index'])->name('raffles.index');
Route::get('/raffles/{slug}', [RaffleController::class, 'show'])->name('raffles.show');

// Verkäufer-Bereich (nur für Verkäufer und Admins)
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
    
    // Produktverwaltung
    Route::get('/products', [SellerController::class, 'products'])->name('products.index');
    Route::get('/products/{id}', [SellerController::class, 'show'])->name('products.show');
    
    // Analytics
    Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');
    
    // Später: Produkterstellung
    // Route::get('/products/create', [SellerController::class, 'create'])->name('products.create');
    // Route::post('/products', [SellerController::class, 'store'])->name('products.store');
    // Route::get('/products/{id}/edit', [SellerController::class, 'edit'])->name('products.edit');
    // Route::put('/products/{id}', [SellerController::class, 'update'])->name('products.update');
});