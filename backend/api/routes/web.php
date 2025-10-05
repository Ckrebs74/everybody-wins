<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RaffleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WalletController;
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
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    
    // Tickets
    Route::post('/raffles/{raffle}/buy', [TicketController::class, 'purchase'])->name('tickets.purchase');
    Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.index');
});

// Public Raffle Routes
Route::get('/raffles', [RaffleController::class, 'index'])->name('raffles.index');
Route::get('/raffles/{raffle}', [RaffleController::class, 'show'])->name('raffles.show');