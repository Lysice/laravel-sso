<?php

/**
 * Routes which is neccessary for the SSO server.
 */

Route::middleware('api')->prefix('api/sso')->group(function () {
    Route::post('login', 'Lysice\LaravelSSO\Controllers\ServerController@login');
    Route::post('logout', 'Lysice\LaravelSSO\Controllers\ServerController@logout');
    Route::get('attach', 'Lysice\LaravelSSO\Controllers\ServerController@attach');
    Route::get('userInfo', 'Lysice\LaravelSSO\Controllers\ServerController@userInfo');
});
