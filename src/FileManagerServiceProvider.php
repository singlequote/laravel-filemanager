<?php

namespace SingleQuote\FileManager;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //where the routes are
        $this->loadRoutesFrom(__DIR__.'/routes/laravel-filemanager.php');

        //where the views are
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-filemanager');

        //publish the routes
        $this->publishes([
            __DIR__.'/../config/laravel-filemanager.php' => config_path('laravel-filemanager.php')
        ], 'config');

        //publish the views
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-filemanager'),
        ]);

        $this->publishes([
            __DIR__.'/resources/css' => public_path('vendor/laravel-filemanager/css'),
            __DIR__.'/resources/js' => public_path('vendor/laravel-filemanager/js'),
        ], 'public');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('FileManager', 'SingleQuote\FileManager\FileManager');
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-filemanager.php', 'laravel-filemanager'
        );
    }
}
