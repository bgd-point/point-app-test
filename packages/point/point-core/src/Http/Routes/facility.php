<?php
Route::group(['middleware' => 'auth', 'prefix' => 'facility', 'namespace' => 'Point\Core\Http\Controllers\Facility'], function () {
    Route::get('/monitoring', 'MonitoringController@index');
    Route::get('/monitoring/more', 'MonitoringController@more');
    Route::get('/monitoring/search', 'MonitoringController@search');
});
