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

Route::group(['namespace' => 'Point\Framework\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('inventory', 'MenuController@inventory');
        Route::get('expedition', 'MenuController@expedition');
        Route::get('purchasing', 'MenuController@purchasing');
        Route::get('sales', 'MenuController@sales');
        Route::get('manufacture', 'MenuController@manufacture');
        Route::get('finance', 'MenuController@finance');
        Route::get('accounting', 'MenuController@accounting');
    });

    Route::get('formulir/{id}/approval/check-status/{token}', 'FormulirController@checkApprovalStatus');
    
    Route::get('formulir/{id}/cancel/status/{token}', 'FormulirController@checkCancelStatus');
    Route::get('formulir/{id}/cancel/approve/{token}', 'FormulirController@cancelApproved');
    Route::get('formulir/{id}/cancel/reject/{token}', 'FormulirController@cancelRejected');

    Route::group(['middleware' => 'auth'], function () {

        // formulir request routes
        Route::group(['prefix' => 'formulir'], function () {
            // default formulir managemenet ajax request
            Route::post('cancel', 'FormulirController@cancel');
            Route::post('requestCancel', 'FormulirController@requestCancel');
            Route::post('close', 'FormulirController@close');
            Route::post('reopen', 'FormulirController@reopen');
            Route::get('access/{title}/{permission_type}', 'FormulirController@access');
            Route::post('access/toggle', 'FormulirController@toggleAccess');

            // storage formulir management ajax request
            Route::post('upload/{form}/{id}', 'FormulirController@upload');
            Route::get('download/{form}/{id}/{name}', 'FormulirController@download');
            Route::get('delete/{form}/{id}/{name}', 'FormulirController@delete');
        });

        // formulir request routes
        Route::group(['prefix' => 'temporary-access'], function () {
            Route::get('{title}/{permission_type}', 'TemporaryAccessController@index');
            Route::post('toggle', 'TemporaryAccessController@toggleAccess');
        });
    });
});
