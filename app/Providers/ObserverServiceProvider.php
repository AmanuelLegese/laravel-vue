<?php

namespace App\Providers;

use App\Models\Transaction;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        Transaction::observe(\App\Observers\TransactionObserver::class);
    }
}
