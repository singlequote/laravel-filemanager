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

        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'filemanager');

        //php artisan vendor:publish --tag=laravel-filemanager-resource
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-filemanager'),
        ], 'laravel-filemanager-resource');
        
        //php artisan vendor:publish --tag=laravel-filemanager-config
        $this->publishes([
            __DIR__.'/../config/laravel-filemanager.php' => config_path('laravel-filemanager.php'),
        ], 'laravel-filemanager-config');
        
        //php artisan vendor:publish --tag=laravel-filemanager-assets
        $this->publishes([
            __DIR__.'/resources/dist/filemanager.min.js' => public_path('vendor/laravel-filemanager/filemanager.min.js'),
            __DIR__.'/resources/dist/filemanager.min.css'    => public_path('vendor/laravel-filemanager/filemanager.min.css'),
        ], 'laravel-filemanager-assets');
        
        //php artisan vendor:publish --tag=laravel-filemanager-locale
        $this->publishes([
           __DIR__.'/resources/lang' => resource_path('lang/vendor/laravel-filemanager/'),
        ], 'laravel-filemanager-locale');
        
        //php artisan vendor:publish --tag=laravel-filemanager-images
        $this->publishes([
           __DIR__.'/resources/images' => public_path('vendor/laravel-filemanager/images'),
        ], 'laravel-filemanager-images');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('FileManager', 'SingleQuote\FileManager\FileManager');

        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-filemanager.php',
            'laravel-filemanager'
        );
    }
}
