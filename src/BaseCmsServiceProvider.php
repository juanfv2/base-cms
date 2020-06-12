<?php

namespace Juanfv2\BaseCms;

use Illuminate\Support\ServiceProvider;

class BaseCmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->make('Juanfv2\BaseCms\CalculatorController');
        // $this->app->make('Juanfv2\BaseCms\Controllers\Country\CountryAPIController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/data' => database_path('data'),
            __DIR__ . '/../database/seeds' => database_path('seeds'),
        ], 'base-cms-migrations');
    }
}
