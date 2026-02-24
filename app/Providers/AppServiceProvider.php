<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CloudinaryService;
use App\Services\BookingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register singleton services for performance
        $this->app->singleton(CloudinaryService::class, function ($app) {
            return new CloudinaryService();
        });

        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set the default timezone
        date_default_timezone_set('Asia/Jakarta');
    }
}
