<?php

namespace SingleQuote\FileManager\Observers;

class FileObserver
{
    
    /**
     * Create observer
     * 
     * @param object $config
     */
    public static function create(object $config)
    {
        cache()->tags([
            'laravel-filemanager:files',  
            'laravel-filemanager:disk-size'
        ])->flush();
    }
    
    /**
     * Update observer
     * 
     * @param object $config
     */
    public static function update(object $config)
    {
        cache()->tags([
            'laravel-filemanager:files',  
            'laravel-filemanager:disk-size'
        ])->flush();
    }
    
    /**
     * Observer delete
     * 
     * @param object $config
     */
    public static function delete(object $config)
    {
        cache()->tags([
            'laravel-filemanager:files',  
            'laravel-filemanager:disk-size'
        ])->flush();
    }
    
}