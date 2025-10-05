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
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::get('/wallet/deposit/success', [WalletController::class, 'depositSuccess'])->name('wallet.deposit.success');
    Route::get('/wallet/withdraw', [WalletController::class, 'showWithdraw'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw.post');
    
    // Tickets
    Route::post('/raffles/{raffle}/buy', [TicketController::class, 'purchase'])->name('tickets.purchase');
    Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.index');
});

// Public Raffle Routes - WICHTIG: Slug statt ID!
Route::get('/raffles', [RaffleController::class, 'index'])->name('raffles.index');
Route::get('/raffles/{slug}', [RaffleController::class, 'show'])->name('raffles.show');

// Verkäufer-Bereich (nur für Verkäufer und Admins)
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    
    // Dashboard - KORRIGIERT: index() statt dashboard()
    Route::get('/dashboard', [SellerController::class, 'index'])->name('dashboard');
    
    // Produktverwaltung
    Route::get('/products', [SellerController::class, 'products'])->name('products.index');
    Route::get('/products/{id}', [SellerController::class, 'show'])->name('products.show');
    
    // Analytics
    Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');
});

// Multi-Step Produkterstellung - KORRIGIERT: Eigene Gruppe ohne doppelte Verschachtelung
Route::middleware(['auth', 'seller'])->prefix('seller/products')->name('seller.products.')->group(function () {
    
    // SCHRITT 1: Kategorie & Typ auswählen
    Route::get('/create', [SellerController::class, 'create'])->name('create');
    Route::post('/create/step1', [SellerController::class, 'storeStep1'])->name('create.step1');
    
    // SCHRITT 2-5: Weitere Schritte anzeigen
    Route::get('/create/step/{step}', [SellerController::class, 'showStep'])
        ->where('step', '[2-5]')
        ->name('create.step');
    
    // SCHRITT 2: Produktdetails speichern
    Route::post('/create/step2', [SellerController::class, 'storeStep2'])->name('create.step2');
    
    // SCHRITT 3: Media-Upload (mit erhöhtem Rate Limit)
    Route::post('/create/step3', [SellerController::class, 'storeStep3'])
        ->middleware('throttle:20,1')
        ->name('create.step3');
    
    // SCHRITT 4: Preisgestaltung speichern
    Route::post('/create/step4', [SellerController::class, 'storeStep4'])->name('create.step4');
    
    // SCHRITT 5: Veröffentlichen
    Route::post('/create/step5', [SellerController::class, 'storeStep5'])->name('create.step5');
    
    // AJAX ENDPOINTS
    Route::post('/auto-save', [SellerController::class, 'autoSave'])
        ->middleware('throttle:60,1')
        ->name('auto-save');
    
    Route::post('/suggest-price', [SellerController::class, 'suggestPrice'])
        ->middleware('throttle:30,1')
        ->name('suggest-price');
    
    Route::delete('/media/{id}', [SellerController::class, 'deleteMedia'])->name('delete-media');
    Route::post('/media/reorder', [SellerController::class, 'reorderMedia'])->name('reorder-media');
});