# Laravel filemanager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)

This package contains a light weight filemanager. It is build for easy intergration and easy to customize. It works only with laravel and uses a lot of laravels code. The code is clean and we want to keep it this way. offcourse this package will grow into an awesome filemanager where you can manage your files the way you want.

## Installation

You can install the package via composer:

```php
composer require singlequote/laravel-filemanager

php artisan vendor:publish --tag=public --force
php artisan vendor:publish --tag=config
```

### Why use this? 
Well let me tell you. This package has its own media viewer. This means you can open files in every view wherever you want. Also the media viewer optimizes your images to fit every weird structure you make. And yes the filemanager makes it easy as well! Manage your private files or share it with other users!

## Todo list
Every new version release we create a new todo list. And yes you can help with that! Create a merge request with fixes, feutures and whatever you code.
#### Version 1.0.0 - Release
[ ] Creating docs
[ ] Creating tests
[ ] Intergrating permissions 
[ ] Stand alone button for image picker
[ ] Ability to crop images

## Usage

### Media viewer
Yes this package has its own media viewer and i know thats awesome. If you want to show images from your filemanager you can use the media route. in the  filemanager config file there is a line `media.prefix` this is your route name. The default value is `media`

Accepting paramaters :
* Height (h = 100)
* Width (w = 100)
* Quality (q = 100) in %

```php
    // The path to your file is the path starting from your filemanagers root 
    
    <img src='{{route('media',  'path/to/image.png')}}'> //This uses the original size and quality of the image
    
    <img src='{{route('media',  'path/to/image.png')}}?h=100&w=100&q=50'> //this creates a nice thumb image 100x100 with 50% of the quality. This is good for performance on big files
```

### Filemanager
Lets keep it all simple. For this package you need a view and some require html. When you publish the resources, you get a demo page with all the basics.
```html
<div class='row' id="filemanager">
    <!--Sidebar-->
    <div class='col-3'>
        <div class="card bg-default">
            <div class="card-header">Sidebar</div>
            <div class="card-body">
              <div id='filemanager-sidebar'></div>
            </div>
        </div>
    </div>

    <!--Files content-->
    <div class='col'>
        <div class="card text-white bg-default">
            <div class="card-header">Content</div>
            <div class="card-body">
              <div id='filemanager-content'></div>
            </div>
        </div>
    </div>
</div>

<!--The modal needed for the package-->
<div class="modal fade modal-primary" id="filemanager-media-preview" tabindex="-1" role="dialog" aria-labelledby="filemanager-media-preview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body text-default"></div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
```
And also include the scripts and css
```html
<!--Bootstrap and stuff-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://bootswatch.com/4/flatly/bootstrap.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<!--This is required for a nice view-->
<link type='text/css' rel='stylesheet' href='{{asset('vendor/laravel-filemanager/css/filemanager.css')}}' />
```
And the scripts
```html
<!--Kinda required! Jquery is needed and bootstrap is just easy-->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<!-- Required script and calls -->
<script type='text/javascript' src='{{asset('vendor/laravel-filemanager/js/filemanager.min.js')}}'></script>
<script type='text/javascript'>
    filemanager = new FileManager;
    filemanager.root = "{{Auth::user()->id}}";
    filemanager.token = "{{csrf_token()}}";
</script>
```
`Thats all!` Have fun using it



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
- [Amando Vledder](https://github.com/AmandoVledder)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
