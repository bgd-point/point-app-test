<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // DOWNPAYMENT
    Route::get('/downpayment/approve-all', 'DownpaymentApprovalController@approveAll');
    Route::get('/downpayment/reject-all', 'DownpaymentApprovalController@rejectAll');
    Route::any('/downpayment/{id}/approve', 'DownpaymentApprovalController@approve');
    Route::any('/downpayment/{id}/reject', 'DownpaymentApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/downpayment/vesa-rejected', 'DownpaymentVesaController@rejected');
        Route::get('/downpayment/vesa-approval', 'DownpaymentVesaController@approval');
        Route::get('/downpayment/vesa-create', 'DownpaymentVesaController@create');
        

        Route::get('/downpayment/request-approval', 'DownpaymentApprovalController@requestApproval');
        Route::post('/downpayment/send-request-approval', 'DownpaymentApprovalController@sendRequestApproval');
        Route::get('/downpayment/create-from-cutoff', 'DownpaymentController@createFromCutoff');
        Route::get('/downpayment/select-cutoff', 'DownpaymentController@_selectCutoff');
        Route::get('/downpayment/select-account', 'DownpaymentController@_selectAccount');
        Route::get('/downpayment/select-customer', 'DownpaymentController@_selectCustomer');

        Route::get('/downpayment/{id}/archived', 'DownpaymentController@archived');
        Route::get('/downpayment/insert/{id}', 'DownpaymentController@insert');
        Route::resource('/downpayment', 'DownpaymentController');
    });
});
