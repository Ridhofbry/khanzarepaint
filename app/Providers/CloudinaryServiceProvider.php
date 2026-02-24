<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CloudinaryLabs\CloudinaryLaravel\CloudinaryEngine;

class CloudinaryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('cloudinary', function ($app) {
            return new CloudinaryEngine(
                config('cloudinary.cloud_name'),
                config('cloudinary.api_key'),
                config('cloudinary.api_secret')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
