<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaffleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// =====================================================
// Homepage - MIT korrekter Bildladung wie bei /raffles
// =====================================================
Route::get('/', function () {
    // Lade Raffles mit Product UND dessen Images (wie in RaffleController)
    $activeRaffles = \App\Models\Raffle::with([
        'product',
        'product.seller',
        'product.images'  // <-- Wichtig: Nested eager loading
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
    Route::post('/deposit', [DashboardController::class, 'deposit'])->name('deposit');
    
    // Tickets kaufen
    Route::post('/raffles/{raffle}/buy-ticket', [TicketController::class, 'purchase'])
        ->name('raffles.buy-ticket');
});

// Public Raffle Routes
Route::get('/raffles', [RaffleController::class, 'index'])->name('raffles.index');
Route::get('/raffles/{raffle}', [RaffleController::class, 'show'])->name('raffles.show');