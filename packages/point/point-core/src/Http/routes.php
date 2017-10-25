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

Route::group(['namespace' => 'Point\Core\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('master', function () {
            return view('core::app.master.menu');
        });
        Route::get('facility', function () {
            return view('core::app.facility.menu');
        });

        // Download File
        Route::get('download/{project}/{folder}/{name}', 'DownloadFileController@download');
    });

    Route::get('error', function () {
        return view('core::errors.index');
    });
    Route::get('restricted', function () {
        return view('core::errors.restricted');
    });

    // authentication routes
    Route::group(['namespace' => 'Auth'], function () {
        // login and logout
        Route::get('auth/login', 'AuthController@getLogin');
        Route::post('auth/login', 'AuthController@postLogin');
        Route::get('auth/logout', 'AuthController@getLogout');

        // password reset link request routes
        Route::get('password/email', 'PasswordController@getEmail');
        Route::post('password/email', 'PasswordController@postEmail');

        // password reset routes
        Route::get('password/reset/{token}', 'PasswordController@getReset');
        Route::post('password/reset', 'PasswordController@postReset');
    });

    // setting routes
    Route::group(['middleware' => 'auth', 'prefix' => 'settings'], function () {
        // themes routes
        Route::get('/', 'Setting\ThemesController@index');

        // password routes
        Route::get('/password', 'Setting\PasswordController@index');
        Route::post('/password', 'Setting\PasswordController@changePassword');

        // config routes
        Route::get('/config', 'Setting\ConfigController@index');
        Route::get('/config/date-input', 'Setting\ConfigController@dateInput');
        Route::get('/config/date-show', 'Setting\ConfigController@dateShow');
        Route::get('/config/mouse-select-allowed', 'Setting\ConfigController@mouseSelectAllowed');
        Route::get('/config/right-click-allowed', 'Setting\ConfigController@rightClickAllowed');
        Route::get('/config/user-change-password-allowed', 'Setting\ConfigController@userChangePasswordAllowed');
        Route::get('/config/user-guide-helper', 'Setting\ConfigController@userGuideHelper');
        Route::get('/config/pos-font-size', 'Setting\ConfigController@posFontSize');

        // notification routes
        Route::get('/notification', 'Setting\NotificationController@index');

        // end notes routes
        Route::post('/end-notes/update', 'Setting\EndNotesController@update');
        Route::get('/end-notes', 'Setting\EndNotesController@index');

        // company logo
        Route::post('/logo/insert', 'Setting\LogoController@store');
        Route::get('/logo', 'Setting\LogoController@index');

        // reset database
        Route::get('/reset-database', 'Setting\ResetDatabaseController@index');
        Route::post('/reset-database/to-default', 'Setting\ResetDatabaseController@toDefault');
    });
    
    Route::group(['middleware' => 'auth'], function () {
        Route::get('logo/{url}/{name}', function ($url, $name) {
            $path = storage_path().'/app/'.$url.'/logo/' . $name;
            if (file_exists($path)) {
                return \Response::download($path);
            }
        });
    });
    

    Route::group(['middleware' => 'auth'], function () {
        // bugs report routes
        Route::post('/bug-report', 'BugReportController@_send');
    });

    Route::group(['middleware' => 'auth'], function () {
        // bugs report routes
        Route::post('/temp/add', 'TempController@add');
        Route::get('/temp/clear', 'TempController@clear');
        Route::get('/temp/cancel', 'TempController@cancel');
    });
});
