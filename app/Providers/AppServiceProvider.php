<?php

namespace App\Providers;

use App\Models\Assignments;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
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

        if (filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)) {
            $url->forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            $showEvaluateeNavigation = false;

            if ($user && $user->hasRole('ผู้รับการประเมิน')) {
                $showEvaluateeNavigation = Assignments::where('evaluatee_id', $user->id)->exists();
            }

            $view->with('showEvaluateeNavigation', $showEvaluateeNavigation);
        });
    }
}
