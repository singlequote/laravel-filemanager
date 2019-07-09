<?php
return [
    /**
     * The package prefix
     * Call it like yourwebsite.com/filemanager
     */
    'prefix' => 'filemanager',

    /**
     * The middleware for the routes.
     */
    'middleware' => ['web'],

    /**
     * The filesystems driver
     * Create new drivers here (config/filesystems.php)
     */
    'disk' => 'local',

    /**
     * The path where the driver should locate the files and folders
     *
     */
    'path' => 'media',
    
    /**
     * Set to true if a logged in user can use a personal drive
     */
    'my_drive' => true,
    
    /**
     * Set to true if users can share files in a public drive
     * 
     */
    'public_drive' => true,
    
    /**
     * If set to true, The user must be authenticated to access the public drive
     * 
     */
    'require_authentication_public_drive' => true,
    
    /**
     * Set to true if users can share private files with other users or emails
     * 
     */
    'shared_drive' => true,

    /**
     * Route to redirect the user to when not logged in
     */
    'redirect_not_authenticated' => 'login',
    
    /**
     * Lazy loading on resources
     * 
     * Set the values to false if you don't want to use lazy loading
     * 
     */
    'pagination_results_folders' => 12, //2 rows
    'pagination_results_files' => 18, //3 rows
    
    /**
     * Upload limit for private drive
     * 
     * In KB 1000000 = 1GB
     * 
     */
    'max_upload_drive' => 100000,

    /**
     * Media config
     */
    'media' => [
        /**
         * Set to false if you dont want to use the media
         */
        'enabled' => true,

        /**
         * The prefix for showing media files
         * This is also your route name
         * For example route('media', 'myfolder/my-awesome-file.png') for showing an image
         */
        'prefix' => 'media',

        /**
         * Middleware for showing media files
         */
        'middleware' => ['web'],

        /**
         * When enabled the package will make copies of the requested
         * file end places them in the public folder. Using this the files are
         * loaded faster and the browser caches the images when there is no php required.
         *
         */
        'create_hyperlink' => true,

        /**
         * The driver for resizing the file
         * Supported drivers are fit and resize
         */
        'driver' => 'fit',
    ]
];
