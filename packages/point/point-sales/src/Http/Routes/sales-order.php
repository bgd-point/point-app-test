<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // SALES ORDER
    Route::get('/sales-order/approve-all', 'SalesOrderApprovalController@approveAll');
    Route::get('/sales-order/reject-all', 'SalesOrderApprovalController@rejectAll');
    Route::any('/sales-order/{id}/approve', 'SalesOrderApprovalController@approve');
    Route::any('/sales-order/{id}/reject', 'SalesOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/sales-order/vesa-rejected', 'SalesOrderVesaController@rejected');
        Route::get('/sales-order/vesa-approval', 'SalesOrderVesaController@approval');
        Route::get('/sales-order/vesa-create', 'SalesOrderVesaController@create');

        Route::get('/sales-order/request-approval', 'SalesOrderApprovalController@requestApproval');
        Route::post('/sales-order/send-request-approval', 'SalesOrderApprovalController@sendRequestApproval');
        Route::get('/sales-order/create-step-1', 'SalesOrderController@createStep1');
        Route::get('/sales-order/create-step-2/{id}', 'SalesOrderController@createStep2');
        Route::get('/sales-order/{id}/edit-no-ref', 'SalesOrderController@editNoref');
        Route::post('/sales-order/send-email-order', 'SalesOrderController@sendEmailOrder');
        Route::get('/sales-order/{id}/archived', 'SalesOrderController@archived');
        Route::get('/sales-order/pdf', 'SalesOrderController@indexPDF');
        Route::post('/sales-order/{id}/store', 'SalesOrderController@store');
        Route::post('/sales-order/store', 'SalesOrderController@store');

        Route::resource('/sales-order', 'SalesOrderController');
    });
});
