<?php
Route::group(['prefix' => config('laravel-filemanager.prefix'), 'middleware' => config('laravel-filemanager.middleware')],function () {


    Route::get('/'              , '\SingleQuote\FileManager\Controllers\FileManager@index');

    Route::get('/get/sidebar'   , '\SingleQuote\FileManager\Controllers\FileManager@getSidebar');
    Route::get('/get/content'   , '\SingleQuote\FileManager\Controllers\FileManager@getContent');

    Route::group(['prefix' => 'action'],function () {
        Route::post('upload'    , '\SingleQuote\FileManager\Controllers\FileManager@uploadItem');
        Route::post('edit'      , '\SingleQuote\FileManager\Controllers\FileManager@editItem');
        Route::post('new'       , '\SingleQuote\FileManager\Controllers\FileManager@newItem');
        Route::delete('delete'  , '\SingleQuote\FileManager\Controllers\FileManager@deleteItem');
    });

});


Route::group(['prefix' => config('laravel-filemanager.media.prefix'), 'middleware' => config('laravel-filemanager.media.middleware')],function () {
    Route::get('{file?}'    , '\SingleQuote\FileManager\Controllers\MediaController@getFile')->where('file', '(.*)')->name(config('laravel-filemanager.media.prefix'));
});

