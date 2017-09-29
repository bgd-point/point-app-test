<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // DELIVERY ORDER
    Route::get('/delivery-order/approve-all', 'DeliveryOrderApprovalController@approveAll');
    Route::get('/delivery-order/reject-all', 'DeliveryOrderApprovalController@rejectAll');
    Route::any('/delivery-order/{id}/approve', 'DeliveryOrderApprovalController@approve');
    Route::any('/delivery-order/{id}/reject', 'DeliveryOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/delivery-order/vesa-rejected', 'DeliveryOrderVesaController@rejected');
        Route::get('/delivery-order/vesa-approval', 'DeliveryOrderVesaController@approval');
        Route::get('/delivery-order/vesa-create', 'DeliveryOrderVesaController@create');
        Route::get('/delivery-order/request-approval', 'DeliveryOrderApprovalController@requestApproval');
        Route::post('/delivery-order/send-request-approval', 'DeliveryOrderApprovalController@sendRequestApproval');
        
        Route::get('/delivery-order/{id}/archived', 'DeliveryOrderController@archived');
        Route::get('/delivery-order/create-step-1', 'DeliveryOrderController@createStep1');
        Route::get('/delivery-order/create-step-2/{sales_order_id}/{expedition_id?}', 'DeliveryOrderController@createStep2');
        Route::get('/delivery-order/pdf', 'DeliveryOrderController@indexPDF');
        Route::resource('/delivery-order', 'DeliveryOrderController');
    });
});
