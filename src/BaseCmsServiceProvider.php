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
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
