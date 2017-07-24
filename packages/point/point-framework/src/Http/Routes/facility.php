<?php

Route::group(['middleware' => 'auth', 'prefix' => 'facility', 'namespace' => 'Point\Framework\Http\Controllers\Facility'], function () {
    Route::get('/global-notification', 'GlobalNotificationController@index');
    Route::get('/global-notification/apply', 'GlobalNotificationController@apply');
});
