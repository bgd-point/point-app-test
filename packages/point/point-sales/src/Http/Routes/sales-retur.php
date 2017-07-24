<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // RETURN
    Route::any('/retur/approve-all', 'ReturApprovalController@approveAll');
    Route::any('/retur/reject-all', 'ReturApprovalController@rejectAll');
    Route::any('/retur/{id}/approve', 'ReturApprovalController@approve');
    Route::any('/retur/{id}/reject', 'ReturApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/retur/vesa-approval', 'ReturVesaController@approval');
        Route::get('/retur/vesa-rejected', 'ReturVesaController@rejected');

        Route::get('/retur/{id}/archived', 'ReturController@archived');
        Route::get('/retur/request-approval', 'ReturApprovalController@requestApproval');
        Route::post('/retur/send-request-approval', 'ReturApprovalController@sendRequestApproval');
        Route::get('/retur/create-step-1', 'ReturController@createStep1');
        Route::get('/retur/create-step-2/{person_person_id}', 'ReturController@createStep2');
        Route::post('/retur/{id}/store', 'ReturController@storeRetur');
        Route::resource('/retur', 'ReturController');
    });
});
