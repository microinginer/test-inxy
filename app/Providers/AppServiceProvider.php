<?php

namespace App\Providers;

use App\Services\TransactionService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserService::class, fn() => new UserService());
        $this->app->singleton(TransactionService::class, fn() => new TransactionService());
    }

    public function boot(): void
    {
    }
}
