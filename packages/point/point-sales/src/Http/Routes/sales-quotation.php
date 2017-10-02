<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // SALES QUOTATION
    Route::get('/sales-quotation/approve-all', 'SalesQuotationApprovalController@approveAll');
    Route::get('/sales-quotation/reject-all', 'SalesQuotationApprovalController@rejectAll');
    Route::any('/sales-quotation/{id}/approve', 'SalesQuotationApprovalController@approve');
    Route::any('/sales-quotation/{id}/reject', 'SalesQuotationApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/sales-quotation/vesa-approval', 'SalesQuotationVesaController@approval');
        Route::get('/sales-quotation/vesa-rejected', 'SalesQuotationVesaController@rejected');

        Route::get('/sales-quotation/request-approval', 'SalesQuotationApprovalController@requestApproval');
        Route::post('/sales-quotation/send-request-approval', 'SalesQuotationApprovalController@sendRequestApproval');
        Route::post('/sales-quotation/send-email-quotation', 'SalesQuotationController@sendEmailQuotation');
        Route::get('/sales-quotation/get-last-price', 'SalesQuotationController@_getLastPrice');
        Route::get('/sales-quotation/{id}/archived', 'SalesQuotationController@archived');
        Route::get('/sales-quotation/pdf', 'SalesQuotationController@indexPDF');
        Route::resource('/sales-quotation', 'SalesQuotationController');
    });
});
