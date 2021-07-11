<?php

namespace Juanfv2\BaseCms;

use Illuminate\Support\ServiceProvider;
use Juanfv2\BaseCms\Commands\CreateMenus;
use Juanfv2\BaseCms\Commands\AngularCommand;

class BaseCmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateMenus::class,
                AngularCommand::class
            ]);
        }

        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources', 'base-cms-views');

        $this->publishes([
            __DIR__ . '/Entities/Controllers'  => app_path('Http/Controllers/API'),
            __DIR__ . '/Entities/Models'       => app_path('Models'),
            __DIR__ . '/Entities/Repositories' => app_path('Repositories'),
        ], 'base-cms-entities');

        $this->publishes([
            __DIR__ . '/../config/base-cms.php' => config_path('base-cms.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../database/data'       => database_path('data'),
            __DIR__ . '/../database/factories'  => database_path('factories'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/seeders'    => database_path('seeders'),
        ], 'base-cms-databases');


        $this->publishes([
            __DIR__ . '/../resources/infyom'        => resource_path('infyom'),
            __DIR__ . '/../resources/model_schemas' => resource_path('model_schemas'),
            __DIR__ . '/../resources/lang'          => resource_path('lang'),
            __DIR__ . '/../resources/assets'        => public_path('assets'),
            __DIR__ . '/../resources/base'          => public_path('base'),
        ], 'base-cms-views');
    }
}
