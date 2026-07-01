<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($rootUrl = config('app.url')) {
            URL::forceRootUrl(rtrim($rootUrl, '/'));
        }

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
