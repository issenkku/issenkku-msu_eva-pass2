<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        Paginator::useBootstrapFive();

        if (App::environment('testing')) {
            View::composer('*', function ($view) {
                $view->with('skipVite', true);
            });
        }

        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
