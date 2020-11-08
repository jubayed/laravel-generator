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
                \Jubayed\LaravelGenerator\Commands\GenerateControllerCommand::class,
                \Jubayed\LaravelGenerator\Commands\GenerateViewCommand::class,
                \Jubayed\LaravelGenerator\Commands\GenerateModelCommand::class,
                \Jubayed\LaravelGenerator\Commands\GenerateMigrationCommand::class,
                \Jubayed\LaravelGenerator\Commands\GenerateMvcCommand::class,
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
            $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
            $this->app->register(\KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
        }
    }
}
