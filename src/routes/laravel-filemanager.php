<?php
Route::group(['prefix' => config('laravel-filemanager.prefix'), 'middleware' => config('laravel-filemanager.middleware')],function () {
    
    Route::get('/'              , '\SingleQuote\FileManager\Controllers\FileManager@index')->name(config('laravel-filemanager.prefix'));

    Route::group(['prefix' => 'load'],function () {
        Route::get('configs'            , '\SingleQuote\FileManager\Controllers\FileManager@loadConfigs');

        Route::get('template/{name}'    , '\SingleQuote\FileManager\Controllers\FileManager@loadTemplate');
        Route::get('content'            , '\SingleQuote\FileManager\Controllers\FileManager@loadContent');
        Route::get('sidebar'            , '\SingleQuote\FileManager\Controllers\FileManager@getSidebar');
    });

    Route::group(['prefix' => 'action'],function () {
        Route::post('upload'    , '\SingleQuote\FileManager\Controllers\FileManager@uploadItem');
        Route::post('edit'      , '\SingleQuote\FileManager\Controllers\FileManager@editItem');
        Route::post('create'    , '\SingleQuote\FileManager\Controllers\FileManager@newItem');
        Route::post('resize'    , '\SingleQuote\FileManager\Controllers\FileManager@resize');
        Route::delete('delete'  , '\SingleQuote\FileManager\Controllers\FileManager@deleteItem');
        Route::delete('clear'   , '\SingleQuote\FileManager\Controllers\FileManager@clearCache');
    });

});

/**
 * Media route
 */
Route::group(['prefix' => config('laravel-filemanager.media.prefix'), 'middleware' => config('laravel-filemanager.media.middleware')],function () {
    Route::get('{height?}/{width?}/{file?}'    , '\SingleQuote\FileManager\Controllers\MediaController@getFile')->where('file', '(.*)')->name(config('laravel-filemanager.media.prefix'));
});

