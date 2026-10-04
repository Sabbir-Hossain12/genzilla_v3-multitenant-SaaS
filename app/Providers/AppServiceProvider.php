<?php

namespace App\Providers;

use App\Models\BasicInfo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        View::composer([
            'platform_admin.pages.auth.login',
            'platform_admin.layout.master',
        ], function ($view) {

            // Cache the basic info for 24 hours to prevent hitting the DB on every page load
            $basicInfo = Cache::remember('platform_basic_info', now()->addDay(), function () {
                return BasicInfo::first();
            });

            $view->with('basic_info', $basicInfo);
        });

    }
}
