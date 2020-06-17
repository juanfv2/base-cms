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
        $this->loadViewsFrom(__DIR__ . '/../resources', 'base-cms-views');

        $this->publishes([
            __DIR__ . '/../database/base-cms.php' => config_path('base-cms.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/data' => database_path('data'),
            __DIR__ . '/../database/seeds' => database_path('seeds'),
        ], 'base-cms-migrations');


        $this->publishes([
            __DIR__ . '/../resources/infyom' => resource_path('infyom'),
            __DIR__ . '/../resources/lang' => resource_path('lang'),

            __DIR__ . '/../resources/assets' => public_path('assets'),
            __DIR__ . '/../resources/base' => public_path('base'),
        ], 'base-cms-views');
    }
}
