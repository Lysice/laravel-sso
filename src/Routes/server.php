<?php

/**
 * Routes which is neccessary for the SSO server.
 */

Route::middleware('api')->prefix('api/sso')->group(function () {
    $supports = config('laravel-sso.supports');
    foreach ($supports['attach'] as $method) {
        if (strcmp($method, 'GET') == 0) {
            Route::get('attach', 'Lysice\LaravelSSO\Controllers\ServerController@attach');
        }
        if (strcmp($method, 'POST') == 0) {
            Route::post('attach', 'Lysice\LaravelSSO\Controllers\ServerController@attach');
        }
    }
    foreach ($supports['logout'] as $method) {
        if (strcmp($method, 'GET') == 0) {
            Route::get('logout', 'Lysice\LaravelSSO\Controllers\ServerController@logout');
        }
        if (strcmp($method, 'POST') == 0) {
            Route::post('logout', 'Lysice\LaravelSSO\Controllers\ServerController@logout');
        }

    }

    if(config('laravel-sso.multi_enabled')) {
        Route::get('userInfoMulti', 'Lysice\LaravelSSO\Controllers\ServerController@userInfoMulti');
        Route::post('loginMulti', 'Lysice\LaravelSSO\Controllers\ServerController@loginMulti');
    } else if(config('laravel-sso.query_enabled')) {
        Route::post('loginQuery', 'Lysice\LaravelSSO\Controllers\ServerController@loginQuery');
        Route::get('userInfoMulti', 'Lysice\LaravelSSO\Controllers\ServerController@userInfoMulti');
    } else {
        Route::post('login', 'Lysice\LaravelSSO\Controllers\ServerController@login');
        Route::get('userInfo', 'Lysice\LaravelSSO\Controllers\ServerController@userInfo');
    }

    if(config('laravel-sso.customizeQueryEnabled')) {
        Route::post('customizeQuery', 'Lysice\LaravelSSO\Controllers\ServerController@customizeQuery');
    }
});
