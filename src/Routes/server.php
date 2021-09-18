<?php

/**
 * Routes which is neccessary for the SSO server.
 */

Route::middleware('api')->prefix('api/sso')->group(function () {
    Route::post('logout', 'Lysice\LaravelSSO\Controllers\ServerController@logout');
    Route::get('attach', 'Lysice\LaravelSSO\Controllers\ServerController@attach');
    if(config('laravel-sso.multi_enabled')) {
        Route::get('userInfoMulti', 'Lysice\LaravelSSO\Controllers\ServerController@userInfoMulti');
        Route::post('loginMulti', 'Lysice\LaravelSSO\Controllers\ServerController@loginMulti');
    } else {
        Route::post('login', 'Lysice\LaravelSSO\Controllers\ServerController@login');
        Route::get('userInfo', 'Lysice\LaravelSSO\Controllers\ServerController@userInfo');
    }
});
