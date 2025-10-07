<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\WalletService::class);
        $this->app->singleton(\App\Services\SpendingLimitService::class);
        $this->app->singleton(\App\Services\RaffleDrawService::class);
        $this->app->singleton(\App\Services\PayoutService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
