<?php

namespace Jubayed\LaravelGenerator;

use Illuminate\Support\ServiceProvider;
use \Jubayed\LaravelGenerator\Commands\Scaffold\GenerateControllerCommand;
use \Jubayed\LaravelGenerator\Commands\Scaffold\GenerateViewCommand;
use \Jubayed\LaravelGenerator\Commands\Scaffold\GenerateModelCommand;
use \Jubayed\LaravelGenerator\Commands\Scaffold\GenerateMigrationCommand;
use \Jubayed\LaravelGenerator\Commands\Scaffold\GenerateMvcCommand;

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
                GenerateControllerCommand::class,
                GenerateViewCommand::class,
                GenerateModelCommand::class,
                GenerateMigrationCommand::class,
                GenerateMvcCommand::class,
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
            $this->packages();
        }
    }

    // init pacakge
    private function packages(){
        $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
        $this->app->register(\KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
    }

}
