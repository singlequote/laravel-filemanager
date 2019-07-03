<?php
Route::group(['prefix' => config('laravel-filemanager.prefix'), 'middleware' => config('laravel-filemanager.middleware')],function () {


    Route::get('/'                      , '\SingleQuote\FileManager\FileManager@index')->name(config('laravel-filemanager.prefix'));
    Route::get('config'                 , '\SingleQuote\FileManager\FileManager@getConfig');
    Route::get('load/content'           , '\SingleQuote\FileManager\FileManager@loadContent');
    
    Route::post('upload/files'          , '\SingleQuote\FileManager\FileManager@uploadFiles');
    
    Route::post('create/folder'          , '\SingleQuote\FileManager\FileManager@createFolder');
    
    Route::delete('delete/file'         , '\SingleQuote\FileManager\FileManager@deleteFile');
    Route::delete('delete/folder'       , '\SingleQuote\FileManager\FileManager@deleteFolder');
    
    Route::post('details/file'          , '\SingleQuote\FileManager\FileManager@detailsFile');
    
    Route::post('share/content'         , '\SingleQuote\FileManager\FileManager@shareContent');
    
    Route::put('rename/file'            , '\SingleQuote\FileManager\FileManager@renameFile');
    
    
    Route::get('laravel-datatables.js'  , '\SingleQuote\FileManager\FileManager@getScript');
    Route::get('laravel-datatables.css'  , '\SingleQuote\FileManager\FileManager@getStyle');
});

/**
 * Media route
 */
Route::group(['prefix' => config('laravel-filemanager.media.prefix'), 'middleware' => config('laravel-filemanager.media.middleware')],function () {
    Route::get('{height?}/{width?}/{file?}'    , '\SingleQuote\FileManager\Controllers\MediaController@getFile')->where('file', '(.*)')->name(config('laravel-filemanager.media.prefix'));
});
