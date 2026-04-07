<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\OfficeSwitcherComposer;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \URL::forceRootUrl(\Config::get('app.url'));

        if (\Str::contains(\Config::get('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('adminlte::partials.navbar.navbar', OfficeSwitcherComposer::class);
    }
}