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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set the CA bundle for SSL verification locally on Windows
        if ($this->app->environment('local') && is_file($path = storage_path('app/cacert.pem'))) {
            ini_set('openssl.cafile', $path);
            ini_set('curl.cainfo', $path);
        }
    }
}
