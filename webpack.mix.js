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

mix.sass('public/vendor/laravel-filemanager/css/filemanager.scss', 'public/vendor/laravel-filemanager/css');

mix.babel([
    'public/vendor/laravel-filemanager/js/dropzone.js',
    'public/vendor/laravel-filemanager/js/sweetalert.js',
    'public/vendor/laravel-filemanager/js/filemanager.js'
], 'public/vendor/laravel-filemanager/js/filemanager.min.js');
