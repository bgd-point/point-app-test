<?php

Route::group(['prefix' => 'sales/point/service', 'namespace' => 'Point\PointSales\Http\Controllers\Service'], function () {
    Route::get('/', function () {
        return view('point-sales::app.sales.point.service.menu');
    });

    Route::get('/report/export', 'ServiceReportController@export');
    Route::get('/report/{service_id}', 'ServiceReportController@detail');
    Route::get('/report', 'ServiceReportController@index');

    // INVOICE
    Route::group(['middleware' => 'auth'], function () {
        Route::post('/invoice/send-email', 'InvoiceController@sendEmail');
        Route::get('/invoice/{id}/export', 'InvoiceController@exportPDF');
        Route::get('/invoice/vesa-create', 'ServiceInvoiceVesaController@create');
        Route::get('/invoice/vesa-rejected', 'ServiceInvoiceVesaController@create');
        Route::get('/invoice/{id}/archived', 'InvoiceController@archived');
        Route::get('/invoice/pdf', 'InvoiceController@indexPDF');
        Route::get('/invoice/detail/{id}', 'InvoiceController@ajaxDetailItem');
        Route::resource('/invoice', 'InvoiceController');
    });

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
        Route::get('/downpayment/{id}/archived', 'DownpaymentController@archived');
        Route::get('/downpayment/pdf', 'DownpaymentController@indexPDF');
        Route::get('/downpayment/insert', 'DownpaymentController@insert');
        Route::resource('/downpayment', 'DownpaymentController');
    });

    // PAYMENT COLLECTION
    Route::any('/payment-collection/approve-all', 'PaymentCollectionApprovalController@approveAll');
    Route::any('/payment-collection/reject-all', 'PaymentCollectionApprovalController@rejectAll');
    Route::any('/payment-collection/{id}/approve', 'PaymentCollectionApprovalController@approve');
    Route::any('/payment-collection/{id}/reject', 'PaymentCollectionApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/payment-collection/vesa-rejected', 'PaymentCollectionVesaController@rejected');
        Route::get('/payment-collection/vesa-approval', 'PaymentCollectionVesaController@approval');
        Route::get('/payment-collection/vesa-create', 'PaymentCollectionVesaController@create');
        
        Route::post('/payment-collection/cancel', 'PaymentCollectionController@cancel');
        Route::get('/payment-collection/request-approval', 'PaymentCollectionApprovalController@requestApproval');
        Route::post('/payment-collection/send-request-approval', 'PaymentCollectionApprovalController@sendRequestApproval');
        Route::get('/payment-collection/{id}/archived', 'PaymentCollectionController@archived');
        Route::get('/payment-collection/create-step-1', 'PaymentCollectionController@createStep1');
        Route::get('/payment-collection/create-step-2/{person_person_id}', 'PaymentCollectionController@createStep2');
        Route::get('/payment-collection/pdf', 'PaymentCollectionController@indexPDF');
        Route::post('/payment-collection/create-step-3', 'PaymentCollectionController@createStep3');
        Route::post('/payment-collection/send-email-payment', 'PaymentCollectionController@sendEmailPayment');
        Route::post('/payment-collection/{id}/edit-review', 'PaymentCollectionController@editReview');
        Route::post('/payment-collection/{id}/store', 'PaymentCollectionController@storePb');
        Route::get('/payment-collection/detail/{id}', 'PaymentCollectionController@ajaxDetailItem');
        Route::resource('/payment-collection', 'PaymentCollectionController');
    });
});
