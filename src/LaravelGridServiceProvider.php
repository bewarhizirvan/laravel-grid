<?php

namespace BewarHizirvan\LaravelGrid;

use Illuminate\Support\ServiceProvider;

class LaravelGridServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bewarhizirvan');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'bewarhizirvan');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravelgrid.php', 'laravelgrid');

        // Register the service the package provides.
        $this->app->singleton('laravelgrid', function ($app) {
            return new LaravelGrid;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravelgrid'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravelgrid.php' => config_path('laravelgrid.php'),
        ], 'laravelgrid.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/bewarhizirvan'),
        ], 'laravelgrid.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/bewarhizirvan'),
        ], 'laravelgrid.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/bewarhizirvan'),
        ], 'laravelgrid.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
