<?php

Route::group(['prefix' => 'purchasing/point/service', 'namespace' => 'Point\PointPurchasing\Http\Controllers\Service'], function () {
    Route::get('/', function () {
        return view('point-purchasing::app.purchasing.point.service.menu');
    });

    Route::get('/report/export', 'ServiceReportController@export');
    Route::get('/report', 'ServiceReportController@index');

    // INVOICE
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/invoice/vesa-create', 'ServiceInvoiceVesaController@create');
        Route::get('/invoice/vesa-rejected', 'ServiceInvoiceVesaController@create');
        Route::get('/invoice/{id}/export', 'InvoiceController@exportPDF');
        Route::get('/invoice/{id}/archived', 'InvoiceController@archived');
        Route::get('/invoice/pdf', 'InvoiceController@indexPDF');
        Route::post('/invoice/send-email', 'InvoiceController@sendEmail');
        Route::get('/invoice/detail/{id}', 'InvoiceController@ajaxDetailItem');
        Route::resource('/invoice', 'InvoiceController');
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
        Route::get('/downpayment/pdf', 'DownpaymentController@indexPDF');
        Route::get('/downpayment/create', 'DownpaymentController@create');
        Route::resource('/downpayment', 'DownpaymentController');
    });

    // PAYMENT COLLECTION
    Route::get('/payment-order/approve-all', 'PaymentOrderApprovalController@approveAll');
    Route::get('/payment-order/reject-all', 'PaymentOrderApprovalController@rejectAll');
    Route::any('/payment-order/{id}/approve', 'PaymentOrderApprovalController@approve');
    Route::any('/payment-order/{id}/reject', 'PaymentOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/payment-order/vesa-rejected', 'PaymentOrderVesaController@rejected');
        Route::get('/payment-order/vesa-approval', 'PaymentOrderVesaController@approval');
        Route::get('/payment-order/vesa-create', 'PaymentOrderVesaController@create');

        Route::get('/payment-order/request-approval', 'PaymentOrderApprovalController@requestApproval');
        Route::post('/payment-order/send-request-approval', 'PaymentOrderApprovalController@sendRequestApproval');
        Route::post('/payment-order/cancel', 'PaymentOrderController@cancel');
        Route::get('/payment-order/{id}/archived', 'PaymentOrderController@archived');
        Route::get('/payment-order/create-step-1', 'PaymentOrderController@createStep1');
        Route::get('/payment-order/create-step-2/{person_person_id}', 'PaymentOrderController@createStep2');
        Route::get('/payment-order/pdf', 'PaymentOrderController@indexPDF');
        Route::post('/payment-order/create-step-3', 'PaymentOrderController@createStep3');
        Route::post('/payment-order/{id}/edit-review', 'PaymentOrderController@editReview');
        Route::post('/payment-order/{id}/store', 'PaymentOrderController@storePb');
        Route::post('/payment-order/send-email-payment', 'PaymentOrderController@sendEmailPayment');
        Route::resource('/payment-order', 'PaymentOrderController');
    });
});
