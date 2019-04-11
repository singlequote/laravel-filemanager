# Laravel filemanager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/singlequote/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/singlequote/laravel-filemanager)

This package contains a light weight filemanager. It is build for easy intergration and easy to customize. It works only with laravel and uses a lot of laravels code. The code is clean and we want to keep it this way. offcourse this package will grow into an awesome filemanager where you can manage your files the way you want.

## Installation

You can install the package via composer:

```php
composer require singlequote/laravel-filemanager

php artisan vendor:publish --tag=public --force
php artisan vendor:publish --tag=config --force
php artisan vendor:publish --tag=view --force
```

### Why use this? 
Let's see what kind of cool features this package haves.
* Media viewer. Viewing files from your storage with laravel always gives you an headache. So the package manages it all for you. You don't even have to use the filemanager to use the media viewer.
* Fully custimizable. You can publish the views and even the javascript! This gives you the freedom to customize everything.
* No bootstrap just jquery. Yes i know but jquery makes the code cleaner and easier.
* Caching. When you parse images using php it's way too slow. Let me fix that for you with this package. This package creates cache files inside your public folder and calls them directly.
* Cool plugins. Yes there are some default plugins and they are : 
    * Cropper. Crop / rotate / flip your images with the doka plugin
    * Resizer. Resize your images with this plugin
    * Dropzone. Yes this is easy. Upload multiple files easy and fast
    * Codemirror. This is just awesome. You want to edit your files? No problem, this is an online code editor in your package.

Off Course there are a lot more feutures and awesome stuff. You can try it all.

### How to use
Let's start with the basics. This package needs a few simple thing to get it all started. The first element needed is HTML.
This package needs an dom element to parse the views in. By default the package looks for the `#app` element. You can change this below
```html
<div id="app"></div>

<!-- Yes these 2 are required! -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script type='text/javascript' src='{{asset('vendor/laravel-filemanager/js/filemanager.min.js')}}'></script>
```
Now we need to boot the package. 
```javascript
//first boot the package. This loads the configs but does not parse the views!
filemanager.boot();
//then build the package. This parses the views.
filemanager.build();
//You can call this as filemanager.boot().build();
```
Thats the basic. Checkout the features below.

#### Methods

*callbacks*
By default the package uses the feather icons. After the package is parsed you want to load the icons. This can be done with the callback method. This is called everytime you perform an action.
```javascript
filemanager.callback = () => {
    feather.replace(); //this is called evertime you perform an action
};
```

*Livereload*
When multiple users use this package you can activate the live reload method. This reloads your content every few seconds. Don't worry about the performance. The results are cached locally so only new files are requested or when files are deleted.
```javascript
filemanager.livereload = true; //default false
```

#### Setters
If you want to change the default behaviour of the package you can use the setters.

**Change the package dom**

By default the package looks for a dom element with the ID `app`. You can change this.
```javascript
filemanager.package = "#app";
```
**Change the root**
It is possible to change the root path where the package should start checking for files. By default it is your drivers location.
```javascript
filemanager.root = "news"; //the root folder will be /path/to/your/files/news
```

**Change the base url**
By default the package uses the url thats been set in the config file. If you want to change it use the `baseUrl` method
```javascript
filemanager.baseUrl = "/filemanager";
```

**Change the media url**
By default the package uses the url thats been set in the config file. If you want to change it use the `mediaUrl` method
```javascript
filemanager.mediaUrl = "/media";
```

**Edit dom elements**
It's not recommended but it is possible to change the dom elements used by the package. For example the package looks for a dom element with the ID `sidebar` to parse the sidebar in. Below are the default dom elements used by the package
```javascript
filemanager.sidebar = "#filemanager-sidebar";

filemanager.content = "#filemanager-content";
```

**Set csrf token**
Yes the csrf token is required but you don't have to set it when booting up. The package loads the token from the config settings.
```javascript
filemanager.token = "{{ csrf_token() }}";
```

**callback**
Everytime you perform an action, the callback method is called. For example if you use icons you want them to be parsed everytime you perform an action like reloading the content.
```javascript
filemanager.callback = () => {
     feather.replace(); //load the feather icons
 };
```

#### modals
Yes you can open this package inside your modals. First boot the package before you open the modal.
We start with the html
```html
<button id="open-modal">Open the filemanager inside a bootstrap modal</button>
```
```javascript
//Boot the package, load the configs etc...
filemanager.boot();
//open filemanger inside modal
$(document).on('click', '#open-modal', () => {
    //Warning! This only opens the package inside an modal. 
    //If you want the use it to pick files with, use the picker method instead
    filemanager.window('.modal-body', () => {
        $('#popup-modal').modal('show'); //open the bootstrap modal
    });
});
//pick a file from the filemanager
$(document).on('click', '#open-modal', () => {
    filemanager.picker('.modal-body', (response) => {
        $('#popup-modal').modal('show'); //double click a file to return it
    }).result((response) => {
        console.log( resonse ); //the file data including the route and path
    });
});

```
The `filemanager.window` expects 3 parameters. 
* dom element (required)
* config {} (optional)
* callback function (optional)

*Filetype*
If you want to show the users a specific filetype you can pass an option with the filemanager.
The type option returns all files wich mimetype starts with the given value. For example if you want to return all images.
```javascript
//all images
filemanager.picker('.modal-body', {type : "images"} ,(response) => {...
//png only
filemanager.picker('.modal-body', {type : "images/png"} ,(response) => {...
//jpeg only
filemanager.picker('.modal-body', {type : "images/jpeg"} ,(response) => {...
```
*Sizepicker*
For example, you want to pick a file and return the image with the deminsion of `300x300` You can uxse the `picksize` option
This opens an extra modal where you can specify the deminsions you want.
```javascript
filemanager.picker('.modal-body', { picksize : true }, (response) => {
```

[![Image from Gyazo](https://i.gyazo.com/57a4c9a2229fb649ca256f729d1cfb27.png)](https://gyazo.com/57a4c9a2229fb649ca256f729d1cfb27)

[![Image from Gyazo](https://i.gyazo.com/8b7c4638a19a78c6e057f7cf37a7e0f9.png)](https://gyazo.com/8b7c4638a19a78c6e057f7cf37a7e0f9)

[![Image from Gyazo](https://i.gyazo.com/51f474ef22f6ec9ef33e19c91a5e84d2.jpg)](https://gyazo.com/51f474ef22f6ec9ef33e19c91a5e84d2)

[![Image from Gyazo](https://i.gyazo.com/67863033f561bf634ae539a8cc7e8ad4.jpg)](https://gyazo.com/67863033f561bf634ae539a8cc7e8ad4)

[![Image from Gyazo](https://i.gyazo.com/d3a98ef29d0b4a402228026d65fbf4d2.png)](https://gyazo.com/d3a98ef29d0b4a402228026d65fbf4d2)

[![Image from Gyazo](https://i.gyazo.com/249dafbe12b45a76a60a0e2999764ff4.png)](https://gyazo.com/249dafbe12b45a76a60a0e2999764ff4)

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
