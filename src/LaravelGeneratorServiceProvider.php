<?php

namespace Jubayed\LaravelGenerator;

use Illuminate\Support\ServiceProvider;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole() && $this->app->environment('local') ) {
            // Registering package commands.
            $this->commands([
                //\Jubayed\LaravelGenerator\Commands\ViewFormModelCommand::class,
                // \Jubayed\LaravelGenerator\Commands\ModelFromMysqlCommand::class,
                // \Jubayed\LaravelGenerator\Commands\ControllerFromModelCommand::class,
                // mvc
                // \Jubayed\LaravelGenerator\Commands\MvcFromModelCommand::class,
                // \Jubayed\LaravelGenerator\Commands\MvcFromTableCommand::class,
                
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        if ($this->app->environment('local')) {
            $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-generator');
        }
    }
}
