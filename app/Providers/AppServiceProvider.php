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
        // Dynamically bind public path if public_html exists as a sibling directory (cPanel environment)
        $publicHtmlPath = base_path('../public_html');
        if (file_exists($publicHtmlPath) && is_dir($publicHtmlPath)) {
            $this->app->usePublicPath($publicHtmlPath);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
