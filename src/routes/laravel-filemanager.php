<?php
Route::group(['prefix' => config('laravel-filemanager.prefix'), 'middleware' => config('laravel-filemanager.middleware')],function () {


    Route::get('/'                      , '\SingleQuote\FileManager\FileManager@index')->name(config('laravel-filemanager.prefix'));
    Route::get('/modal'                 , '\SingleQuote\FileManager\FileManager@modal')->name(config('laravel-filemanager.prefix').".modal");
    Route::get('config'                 , '\SingleQuote\FileManager\FileManager@loadConfigurations');
    Route::get('load/content'           , '\SingleQuote\FileManager\FileManager@loadContent');
    
    
    Route::get('get/folders'            , '\SingleQuote\FileManager\FileManager@getFolders');
    Route::put('rename/folder'          , '\SingleQuote\FileManager\Controllers\FoldersController@rename');
    Route::post('create/folder'         , '\SingleQuote\FileManager\Controllers\FoldersController@create');
    Route::post('details/folder'        , '\SingleQuote\FileManager\Controllers\FoldersController@details');
    Route::delete('delete/folder'       , '\SingleQuote\FileManager\Controllers\FoldersController@delete');
    
    Route::get('get/files'              , '\SingleQuote\FileManager\FileManager@getFiles');
    Route::put('rename/file'            , '\SingleQuote\FileManager\Controllers\FilesController@rename');
    Route::post('upload/files'          , '\SingleQuote\FileManager\Controllers\FilesController@upload');
    Route::post('details/file'          , '\SingleQuote\FileManager\Controllers\FilesController@details');
    Route::delete('delete/file'         , '\SingleQuote\FileManager\Controllers\FilesController@delete');
    
    
    
    Route::post('share/file'         , '\SingleQuote\FileManager\Controllers\ShareController@file');
    Route::post('share/folder'       , '\SingleQuote\FileManager\Controllers\ShareController@folder');
    Route::delete('shared'           , '\SingleQuote\FileManager\Controllers\ShareController@deleteSharedItems');
    
    Route::get('laravel-datatables-js'  , '\SingleQuote\FileManager\FileManager@getScript');
    Route::get('laravel-datatables-css' , '\SingleQuote\FileManager\FileManager@getStyle');
});

/**
 * Media route
 */
Route::group(['prefix' => config('laravel-filemanager.media.prefix'), 'middleware' => config('laravel-filemanager.media.middleware')],function () {
    Route::get('{height?}/{width?}/{file?}'    , '\SingleQuote\FileManager\Controllers\MediaController@getFile')->where('file', '(.*)')->name(config('laravel-filemanager.media.prefix'));
});
