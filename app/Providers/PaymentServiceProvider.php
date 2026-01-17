<?php

declare(strict_types=1);

namespace App\Providers;

use App\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager;
        });

        // Alias for easier access
        $this->app->alias(PaymentManager::class, 'payment');
    }

    public function boot(): void
    {
        //
    }
}
