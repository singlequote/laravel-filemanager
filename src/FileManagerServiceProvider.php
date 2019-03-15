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
        //php artisan vendor:publish --tag=config --force
        $this->publishes([
            __DIR__.'/../config/laravel-filemanager.php' => config_path('laravel-filemanager.php')
        ], 'config');

        //publish the views and styling
        //php artisan vendor:publish --tag=public --force
        $this->publishes([
            __DIR__.'/resources/css/filemanager.css'    => public_path('vendor/laravel-filemanager/css/filemanager.min.css'),
            __DIR__.'/resources/js/filemanager.min.js'  => public_path('vendor/laravel-filemanager/js/filemanager.min.js'),
            __DIR__.'/resources/views'                  => resource_path('views/vendor/laravel-filemanager'),
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
