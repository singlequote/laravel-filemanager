<?php
return [
    /**
     * The package prefix
     * Call it like yourwebsite.com/filemanager
     */
    'prefix' => 'filemanager',
    /**
     * Set to true if you want your routes to be encrypted
     * Like yourwebsite.com/filemanager?route={encrypted}
     */
    'encrypted' => false,
    /**
     * The middleware for the routes.
     */
    'middleware' => ['web'],
    /**
     * The filesystems driver
     * Create new drivers here (config/filesystems.php)
     */
    'disk' => 'public',
    /**
     * Users settings
     */
    'auth' => [
        /**
         * Set to true if users are able to have private folders
         */
        'private_folder' => true,
        'private_prefix' => '', //leave empty for {user_id}/

        /**
         * Set to true if users are able to share files
         */
        'shared_folder' => true,
        'shared_prefix' => 'shares',
    ],
    /**
     * Enable cache for images and files
     */
    'cache' => [
        'enabled' => false,
        'keepAlive' => 40320 //in seconds
    ],
    /**
     * Media config
     */
    'media' => [
        /**
         * When enabled the package will make copies of the requested
         * file end places them in the public folder. Using this the files are
         * loaded way faster and the browser caches the images when there is no php required.
         *
         */
        'create_hyperlink' => true,
        //The path starting inside the public folder
        'hyperlink_path' => 'cached',
        
        'cache_every_file' => false, //cache every file including image requests without sizes
        /**
         * Set to false if you dont want to use the media
         */
        'enabled' => true,
        /**
         * The driver for resizing the file
         * Supported drivers are fit and resize
         */
        'driver' => 'fit',
        /**
         * Middleware for showing media files
         */
        'middleware' => ['web'],
        /**
         * The prefix for showing media files
         * This is also your route name
         * For example route('media', 'myfolder/my-awesome-file.png') for showing an image
         */
        'prefix' => 'media',
    ]
];
