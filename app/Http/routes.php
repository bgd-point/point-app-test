<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'DashboardController@index');
});

Route::get('barcode', function () {
    return view('barcode');
});

Route::get('mobile-version', function () {
    return redirect()->back()->withCookie(cookie('is-responsive', 1, 3600));
});

Route::get('desktop-version', function () {
    return redirect()->back()->withCookie(cookie('is-responsive', 0, 3600));
});

Route::get('recalculate', function () {
    \Illuminate\Support\Facades\Artisan::queue('dev:recalculate');

    return 'done';
});

Route::get('reallocation', function () {
    \Illuminate\Support\Facades\Artisan::queue('dev:reallocation');

    return 'please wait at least 3 minutes';
});
