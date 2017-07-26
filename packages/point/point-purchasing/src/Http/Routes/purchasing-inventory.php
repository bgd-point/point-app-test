<?php

Route::group(['prefix' => 'purchasing/point', 'namespace' => 'Point\PointPurchasing\Http\Controllers\Inventory'], function () {
    Route::resource('/', 'PurchasingMenuController@index');

    // PURCHASE REQUISITION
    Route::get('/purchase-requisition/reject-all', 'PurchaseRequisitionApprovalController@rejectAll');
    Route::get('/purchase-requisition/approve-all', 'PurchaseRequisitionApprovalController@approveAll');
    Route::any('/purchase-requisition/{id}/approve', 'PurchaseRequisitionApprovalController@approve');
    Route::any('/purchase-requisition/{id}/reject', 'PurchaseRequisitionApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/purchase-requisition/vesa-approval', 'PurchaseRequisitionVesaController@approval');
        Route::get('/purchase-requisition/vesa-rejected', 'PurchaseRequisitionVesaController@rejected');

        Route::get('/purchase-requisition/request-approval', 'PurchaseRequisitionApprovalController@requestApproval');
        Route::post('/purchase-requisition/send-request-approval', 'PurchaseRequisitionApprovalController@sendRequestApproval');
        Route::post('/purchase-requisition/send-email-requisition', 'PurchaseRequisitionController@sendEmailRequisition');
        Route::get('/purchase-requisition/{id}/archived', 'PurchaseRequisitionController@archived');
        Route::resource('/purchase-requisition', 'PurchaseRequisitionController');
    });

    // PURCHASE ORDER
    Route::get('/purchase-order/reject-all', 'PurchaseOrderApprovalController@rejectAll');
    Route::get('/purchase-order/approve-all', 'PurchaseOrderApprovalController@approveAll');
    Route::any('/purchase-order/{id}/approve', 'PurchaseOrderApprovalController@approve');
    Route::any('/purchase-order/{id}/reject', 'PurchaseOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/purchase-order/vesa-approval', 'PurchaseOrderVesaController@approval');
        Route::get('/purchase-order/vesa-rejected', 'PurchaseOrderVesaController@rejected');
        Route::get('/purchase-order/vesa-create', 'PurchaseOrderVesaController@create');

        Route::get('/purchase-order/request-approval', 'PurchaseOrderApprovalController@requestApproval');
        Route::post('/purchase-order/send-request-approval', 'PurchaseOrderApprovalController@sendRequestApproval');
        Route::get('/purchase-order/create-step-1', 'PurchaseOrderController@createStep1');
        Route::get('/purchase-order/create-step-2/{id}', 'PurchaseOrderController@createStep2');
        Route::get('/purchase-order/{id}/archived', 'PurchaseOrderController@archived');
        Route::post('/purchase-order/{id}/store', 'PurchaseOrderController@store');
        Route::get('/purchase-order/basic/{id}/edit', 'PurchaseOrderController@editBasic');
        Route::post('/purchase-order/send-email-order', 'PurchaseOrderController@sendEmailOrder');
        Route::post('/purchase-order/basic/store', 'PurchaseOrderController@store');
        Route::get('/purchase-order/basic/create', 'PurchaseOrderController@create');
        Route::resource('/purchase-order', 'PurchaseOrderController');
    });

    // DOWNPAYMENT
    Route::get('/downpayment/reject-all', 'DownpaymentApprovalController@rejectAll');
    Route::get('/downpayment/approve-all', 'DownpaymentApprovalController@approveAll');
    Route::any('/downpayment/{id}/approve', 'DownpaymentApprovalController@approve');
    Route::any('/downpayment/{id}/reject', 'DownpaymentApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/downpayment/vesa-approval', 'DownpaymentVesaController@approval');
        Route::get('/downpayment/vesa-rejected', 'DownpaymentVesaController@rejected');

        Route::get('/downpayment/request-approval', 'DownpaymentApprovalController@requestApproval');
        Route::post('/downpayment/send-request-approval', 'DownpaymentApprovalController@sendRequestApproval');
        Route::get('/downpayment/{id}/archived', 'DownpaymentController@archived');
        Route::get('/downpayment/create/{id?}', 'DownpaymentController@create');
        Route::resource('/downpayment', 'DownpaymentController');
    });

    // GOODS RECEIVED
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/goods-received/vesa-approval', 'GoodsReceivedVesaController@approval');
        Route::get('/goods-received/vesa-create', 'GoodsReceivedVesaController@create');
        Route::get('/goods-received/vesa-rejected', 'GoodsReceivedVesaController@rejected');

        Route::get('/goods-received/{id}/archived', 'GoodsReceivedController@archived');
        Route::get('/goods-received/create-step-1', 'GoodsReceivedController@createStep1');
        Route::get('/goods-received/create-step-2/{supplier_id}', 'GoodsReceivedController@createStep2');
        Route::get('/goods-received/create-step-3/{purchase_order_id}', 'GoodsReceivedController@createStep3');
        Route::get('/goods-received/create-step-4/{purchase_order_id}/{group_expedition?}', 'GoodsReceivedController@createStep4');
        Route::resource('/goods-received', 'GoodsReceivedController');
    });

    // INVOICE
    Route::get('/invoice/reject-all', 'InvoiceApprovalController@rejectAll');
    Route::get('/invoice/approve-all', 'InvoiceApprovalController@approveAll');
    Route::any('/invoice/{id}/approve', 'InvoiceApprovalController@approve');
    Route::any('/invoice/{id}/reject', 'InvoiceApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/invoice/vesa-create', 'InvoiceVesaController@create');
        // BASIC INVOICE
        Route::get('/invoice/basic/unit', 'Basic\InvoiceController@_unit');
        Route::get('/invoice/basic/create', 'Basic\InvoiceController@create');
        Route::get('/invoice/basic/{id}/archived', 'Basic\InvoiceController@archived');
        Route::get('/invoice/basic/{id}/edit', 'Basic\InvoiceController@edit');
        Route::put('/invoice/basic/{id}', 'Basic\InvoiceController@update');
        Route::get('/invoice/basic/{id}/show', 'Basic\InvoiceController@show');
        Route::post('/invoice/basic/store', 'Basic\InvoiceController@store');
        Route::get('/invoice/basic', 'Basic\InvoiceController@index');

        Route::get('/invoice/{id}/archived', 'InvoiceController@archived');
        Route::get('/invoice/create-step-1', 'InvoiceController@createStep1');
        Route::get('/invoice/create-step-2/{person_supplier_id}', 'InvoiceController@createStep2');
        Route::get('/invoice/create-step-3', 'InvoiceController@createStep3');
        Route::post('/invoice/send-email', 'InvoiceController@sendEmail');
        Route::resource('/invoice', 'InvoiceController');
    });

    // RETURN
    Route::get('/retur/reject-all', 'ReturApprovalController@rejectAll');
    Route::get('/retur/approve-all', 'ReturApprovalController@approveAll');
    Route::any('/retur/{id}/approve', 'ReturApprovalController@approve');
    Route::any('/retur/{id}/reject', 'ReturApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/retur/vesa-approval', 'ReturVesaController@approval');
        Route::get('/retur/vesa-rejected', 'ReturVesaController@rejected');
        Route::get('/retur/vesa-create', 'ReturVesaController@create');

        Route::get('/retur/{id}/archived', 'ReturController@archived');
        Route::get('/retur/request-approval', 'ReturApprovalController@requestApproval');
        Route::post('/retur/send-request-approval', 'ReturApprovalController@sendRequestApproval');
        Route::get('/retur/create-step-1', 'ReturController@createStep1');
        Route::get('/retur/create-step-2/{person_supplier_id}', 'ReturController@createStep2');
        Route::post('/retur/{id}/store', 'ReturController@storeRetur');
        Route::resource('/retur', 'ReturController');
    });

    // PAYMENT ORDER
    Route::get('/payment-order/basic/reject-all', 'Basic\PaymentOrderApprovalController@rejectAll');
    Route::get('/payment-order/basic/approve-all', 'Basic\PaymentOrderApprovalController@approveAll');
    Route::any('/payment-order/basic/{id}/approve', 'Basic\PaymentOrderApprovalController@approve');
    Route::any('/payment-order/basic/{id}/reject', 'Basic\PaymentOrderApprovalController@reject');
    
    Route::get('/payment-order/reject-all', 'PaymentOrderApprovalController@rejectAll');
    Route::get('/payment-order/approve-all', 'PaymentOrderApprovalController@approveAll');
    Route::any('/payment-order/{id}/approve', 'PaymentOrderApprovalController@approve');
    Route::any('/payment-order/{id}/reject', 'PaymentOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/payment-order/basic/vesa-approval', 'Basic\PaymentOrderVesaController@approval');
        Route::get('/payment-order/basic/vesa-rejected', 'Basic\PaymentOrderVesaController@rejected');
        Route::get('/payment-order/basic/vesa-create', 'Basic\PaymentOrderVesaController@create');
        Route::get('/payment-order/basic/request-approval', 'Basic\PaymentOrderApprovalController@requestApproval');
        Route::post('/payment-order/basic/send-request-approval', 'Basic\PaymentOrderApprovalController@sendRequestApproval');

        Route::get('/payment-order/vesa-approval', 'Basic\PaymentOrderVesaController@approval');
        Route::get('/payment-order/vesa-rejected', 'PaymentOrderVesaController@rejected');
        Route::get('/payment-order/vesa-create', 'PaymentOrderVesaController@create');
        Route::get('/payment-order/request-approval', 'PaymentOrderApprovalController@requestApproval');
        Route::post('/payment-order/send-request-approval', 'PaymentOrderApprovalController@sendRequestApproval');
        
        // BASIC PAYMENT ORDER
        Route::post('/payment-order/basic/cancel', 'Basic\PaymentOrderController@cancel');
        Route::get('/payment-order/basic/{id}/archived', 'Basic\PaymentOrderController@archived');
        Route::get('/payment-order/basic/create-step-1', 'Basic\PaymentOrderController@createStep1');
        Route::get('/payment-order/basic/create-step-2/{person_supplier_id}', 'Basic\PaymentOrderController@createStep2');
        Route::post('/payment-order/basic/create-step-3', 'Basic\PaymentOrderController@createStep3');
        Route::post('/payment-order/basic/send-email-payment', 'Basic\PaymentOrderController@sendEmailPayment');
        Route::post('/payment-order/basic/{id}/edit-review', 'Basic\PaymentOrderController@editReview');
        Route::get('/payment-order/basic/{id}/show', 'Basic\PaymentOrderController@show');
        Route::resource('/payment-order/basic', 'Basic\PaymentOrderController');

        Route::post('/payment-order/cancel', 'PaymentOrderController@cancel');
        Route::get('/payment-order/{id}/archived', 'PaymentOrderController@archived');
        Route::get('/payment-order/create-step-1', 'PaymentOrderController@createStep1');
        Route::get('/payment-order/create-step-2/{person_supplier_id}', 'PaymentOrderController@createStep2');
        Route::post('/payment-order/create-step-3', 'PaymentOrderController@createStep3');
        Route::post('/payment-order/send-email-payment', 'PaymentOrderController@sendEmailPayment');
        Route::post('/payment-order/{id}/edit-review', 'PaymentOrderController@editReview');
        Route::post('/payment-order/{id}/store', 'PaymentOrderController@storePb');
        Route::resource('/payment-order', 'PaymentOrderController');
    });
});
