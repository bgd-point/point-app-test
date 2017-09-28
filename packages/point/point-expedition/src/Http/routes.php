<?php
Route::group(['prefix' => 'expedition/point', 'namespace' => 'Point\PointExpedition\Http\Controllers'], function () {

    // EXPEDITION ORDER
    Route::get('/expedition-order/approve-all', 'ExpeditionOrderApprovalController@approveAll');
    Route::get('/expedition-order/reject-all', 'ExpeditionOrderApprovalController@rejectAll');
    Route::any('/expedition-order/{id}/approve', 'ExpeditionOrderApprovalController@approve');
    Route::any('/expedition-order/{id}/reject', 'ExpeditionOrderApprovalController@reject');
    Route::get('/expedition-order/request-approval', 'ExpeditionOrderApprovalController@requestApproval');
    Route::post('/expedition-order/send-request-approval', 'ExpeditionOrderApprovalController@sendRequestApproval');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/expedition-order/vesa-approval', 'ExpeditionOrderVesaController@approval');
        Route::get('/expedition-order/vesa-rejected', 'ExpeditionOrderVesaController@rejected');
        Route::get('/expedition-order/vesa-create', 'ExpeditionOrderVesaController@create');

        Route::post('/expedition-order/cancel', 'ExpeditionOrderController@cancel');
        Route::get('/expedition-order/{id}/archived', 'ExpeditionOrderController@archived');
        Route::get('/expedition-order/create-step-1', 'ExpeditionOrderController@createStep1');
        Route::get('/expedition-order/create-step-2/{id}', 'ExpeditionOrderController@createStep2');
        Route::get('/expedition-order/load-form-expedition', 'ExpeditionOrderController@loadFormAddExpedition');
        Route::get('/expedition-order/store-expedition', 'ExpeditionOrderController@storeAjaxExpedition');
        Route::post('/expedition-order/send-email-order', 'ExpeditionOrderController@sendEmailOrder');
        Route::post('/expedition-order/{id}/store', 'ExpeditionOrderController@store');
        Route::resource('/expedition-order', 'ExpeditionOrderController');
    });

    // DOWNPAYMENT
    Route::get('/downpayment/approve-all', 'DownpaymentApprovalController@approveAll');
    Route::get('/downpayment/reject-all', 'DownpaymentApprovalController@rejectAll');
    Route::any('/downpayment/{id}/approve', 'DownpaymentApprovalController@approve');
    Route::any('/downpayment/{id}/reject', 'DownpaymentApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/downpayment/vesa-approval', 'DownpaymentVesaController@approval');
        Route::get('/downpayment/vesa-rejected', 'DownpaymentVesaController@rejected');

        Route::get('/downpayment/request-approval', 'DownpaymentApprovalController@requestApproval');
        Route::post('/downpayment/send-request-approval', 'DownpaymentApprovalController@sendRequestApproval');
        Route::get('/downpayment/create/{id}', 'DownpaymentController@create');
        Route::get('/downpayment/{id}/archived', 'DownpaymentController@archived');
        Route::get('/downpayment/create-step-1', 'DownpaymentController@createStep1');
        Route::get('/downpayment/create-step-2/{id}', 'DownpaymentController@createStep2');
        Route::resource('/downpayment', 'DownpaymentController');
    });

    // BASIC INVOICE
    Route::group(['middleware' => 'auth'], function () {
        Route::post('/invoice/basic/store', 'BasicInvoiceController@store');
        Route::post('/invoice/basic/update', 'BasicInvoiceController@update');
        Route::get('/invoice/basic/{id}/archived', 'BasicInvoiceController@archived');
        Route::post('/invoice/basic/send-email', 'BasicInvoiceController@sendEmail');
        Route::resource('/invoice/basic', 'BasicInvoiceController');
    });

    // INVOICE
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/invoice/vesa-rejected', 'InvoiceVesaController@rejected');

        Route::get('/invoice/{id}/export', 'InvoiceController@exportPDF');
        Route::get('/invoice/{id}/archived', 'InvoiceController@archived');
        Route::get('/invoice/create-step-1', 'InvoiceController@createStep1');
        Route::get('/invoice/create-step-2/{person_supplier_id}', 'InvoiceController@createStep2');
        Route::post('/invoice/create-step-3', 'InvoiceController@createStep3');
        Route::post('/invoice/send-email', 'InvoiceController@sendEmail');
        Route::resource('/invoice', 'InvoiceController', ['except' => ['create', 'delete']]);
    });

    // PAYMENT ORDER
    Route::get('/payment-order/approve-all', 'PaymentOrderApprovalController@approveAll');
    Route::get('/payment-order/reject-all', 'PaymentOrderApprovalController@rejectAll');
    Route::any('/payment-order/{id}/approve', 'PaymentOrderApprovalController@approve');
    Route::any('/payment-order/{id}/reject', 'PaymentOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/payment-order/vesa-rejected', 'PaymentOrderVesaController@rejected');
        Route::get('/payment-order/vesa-approval', 'PaymentOrderVesaController@approval');
        Route::get('/payment-order/vesa-create-payment-order', 'PaymentOrderVesaController@createPaymentOrder');
        Route::get('/payment-order/request-approval', 'PaymentOrderApprovalController@requestApproval');
        Route::post('/payment-order/send-request-approval', 'PaymentOrderApprovalController@sendRequestApproval');
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
