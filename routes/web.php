<?php

/**
 * CAPACITY LISTS
 */
Route::group(['namespace' => 'CapacityLists'], function () {
    Route::group(['prefix' => 'capacity-lists', 'as' => 'capacity-lists.',], function () {
        Route::group(['middleware' => ['role:admin']], function () {
            Route::get('index', ['as' => 'index', 'uses' => 'CapacityListsController@index']);
            Route::get('get-lists', ['as' => 'get-lists', 'uses' => 'CapacityListsController@getLists']);
            Route::get('{list?}', ['as' => 'edit', 'uses' => 'CapacityListsController@edit']);
            Route::post('{list?}', ['as' => 'store', 'uses' => 'CapacityListsController@store']);
            Route::delete('{list}', ['as' => 'remove', 'uses' => 'CapacityListsController@delete']);
        });
        Route::get('download/{file}', ['as' => 'download', 'uses' => 'CapacityListsController@download']);
    });

    Route::group([
        'middleware' => ['products:CC']
    ], function () {
        Route::get('capacity-list', ['as' => 'capacity-list.view', 'uses' => 'CapacityListsController@view']);
    });
});

