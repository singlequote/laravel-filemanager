# Laravel filemanager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)

This package contains a light weight filemanager. It is build for easy intergration and easy to customize. It works only with laravel and uses a lot of laravels code. The code is clean and we want to keep it this way. offcourse this package will grow into an awesome filemanager where you can manage your files the way you want.

## Installation

You can install the package via composer:

```php
composer require singlequote/laravel-filemanager

//publish resources
php artisan vendor:publish --tag=laravel-filemanager-resource

//publish assets
php artisan vendor:publish --tag=laravel-filemanager-assets

//publish config
php artisan vendor:publish --tag=laravel-filemanager-config

//publish locale
php artisan vendor:publish --tag=laravel-filemanager-locale

//publish images for file extensions
php artisan vendor:publish --tag=laravel-filemanager-images
```
## Quick start
Download the package, publish the config and the assets (or just the images only) the package will auto require the script and styling files. The default route is  `/filemanager` change it in the config

### Hard cache
The package (when enabled) will cache all the files inside the public folder. To instant load the files, paste this in your `public/.htaccess`

```htaccess
    # Serve cached images if available
    RewriteCond %{DOCUMENT_ROOT}/%{REQUEST_URI} -f
    RewriteRule . %{REQUEST_URI} [L]
```

### To do list

 - [ ] Docs
 - [x] Sharing folders by link
 - [x] Sharing files by link
 - [x] Update design

![Preview 1](https://i.gyazo.com/230b5bbf3828807dd9fee340dae65eb2.jpg)
![Preview 2](https://i.gyazo.com/e21b610a0756a6cc753d830cb7a20a5e.jpg)

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing
Yes please! Finish our work and create a merge request.

### Security

If you discover any security related issues, take a look at the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: ACF Bentveld, Ecu 2 8305 BA, Emmeloord, Netherlands.

## Credits

- [Wim Pruiksma](https://github.com/wimurk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
