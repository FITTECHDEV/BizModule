<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
// use ConsoleTVs\Charts\Registrar as Charts;
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
    public function boot() //Charts $charts
    {
        // $charts->register([
        //     \App\Charts\TicketByCountryChart::class,
        //     \App\Charts\OverallTicketStatus::class,
        //     \App\Charts\MonthWiseTicketsRaised::class,
        //     \App\Charts\TicketStatusForEachCountry::class,
        //     \App\Charts\CountryBasedSolutionReport::class,
        //     \App\Charts\PriorityWiseReport::class,
        //     \App\Charts\DayWiseTicketsRaised::class,
        //     \App\Charts\SolutionWiseTicketsRaised::class,
        // ]);

        \URL::forceRootUrl(\Config::get('app.url'));
        // And this if you wanna handle https URL scheme
        // It's not usefull for http://www.example.com, it's just to make it more independant from the constant value
        if (\Str::contains(\Config::get('app.url'), 'https://')) {
            URL::forceScheme('https');
            //use \URL:forceSchema('https') if you use laravel < 5.4
        }
        //
    }
}
