let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/**
 * Complete build
 */
mix.sass('public/vendor/laravel-filemanager/css/filemanager.scss', 'public/vendor/laravel-filemanager/css/filemanager.css');

/**
 * Only build the filemanager files
 */
mix.babel([
    'public/vendor/laravel-filemanager/js/filemanager.js'
], 'public/vendor/laravel-filemanager/js/filemanager.min.js');

/**
 * Only the dropzone file
 */
mix.babel([
    'public/vendor/laravel-filemanager/js/dropzone.js'
], 'public/vendor/laravel-filemanager/js/dropzone.min.js');

/**
 * Only the sweetalert file
 */
mix.babel([
    'public/vendor/laravel-filemanager/js/sweetalert.js'
], 'public/vendor/laravel-filemanager/js/sweetalert.min.js');

/**
 * Compleet build
 */
mix.babel([
    'public/vendor/laravel-filemanager/js/dropzone.js',
    'public/vendor/laravel-filemanager/js/sweetalert.js',
    'public/vendor/laravel-filemanager/js/filemanager.js'
], 'public/vendor/laravel-filemanager/js/build.min.js');

