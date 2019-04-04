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

        //publish the configs
        //php artisan vendor:publish --tag=config --force
        $this->publishes([
            __DIR__.'/../config/laravel-filemanager.php' => config_path('laravel-filemanager.php')
        ], 'config');


        //publish the styling and scripts
        //php artisan vendor:publish --tag=public --force
        $this->publishes([
            __DIR__.'/resources/js/filemanager.min.js'              => public_path('vendor/laravel-filemanager/js/filemanager.min.js'),
            __DIR__.'/resources/js/plugins/codemirror'              => public_path('vendor/laravel-filemanager/js/codemirror'),
            __DIR__.'/resources/js/plugins/cropper'                 => public_path('vendor/laravel-filemanager/js/cropper'),
            __DIR__.'/resources/js/plugins/dropzone'                => public_path('vendor/laravel-filemanager/js/dropzone'),
            __DIR__.'/resources/js/plugins/resizer'                  => public_path('vendor/laravel-filemanager/js/resizer'),

            __DIR__.'/resources/css/filemanager.min.css'    => public_path('vendor/laravel-filemanager/css/filemanager.min.css'),
            __DIR__.'/resources/css/plugins/codemirror'             => public_path('vendor/laravel-filemanager/css/codemirror'),
            __DIR__.'/resources/css/plugins/dropzone'               => public_path('vendor/laravel-filemanager/css/dropzone'),
            __DIR__.'/resources/css/plugins/cropper'                => public_path('vendor/laravel-filemanager/css/cropper'),
            __DIR__.'/resources/css/plugins/resizer'                => public_path('vendor/laravel-filemanager/css/resizer')
        ], 'public');

        
        //publish the view
        //php artisan vendor:publish --tag=view --force
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-filemanager'),
        ], 'view');
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
