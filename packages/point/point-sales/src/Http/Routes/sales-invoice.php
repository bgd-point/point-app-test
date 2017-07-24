<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // INVOICE
    Route::any('/invoice/{id}/approve', 'InvoiceApprovalController@approve');
    Route::any('/invoice/{id}/reject', 'InvoiceApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/invoice/vesa-create', 'InvoiceVesaController@create');
        Route::get('/invoice/vesa-rejected', 'InvoiceVesaController@create');
        // AJAX GETTING ITEM UNIT
        Route::get('/invoice/item/unit', 'InvoiceController@_unit');

        Route::get('/invoice/basic/create', 'Basic\InvoiceController@create');
        Route::post('/invoice/basic/store', 'Basic\InvoiceController@store');
        Route::get('/invoice/basic/{id}/edit', 'Basic\InvoiceController@edit');
        Route::put('/invoice/basic/{id}', 'Basic\InvoiceController@update');

        Route::get('/invoice/create-step-1', 'InvoiceController@createStep1');
        Route::get('/invoice/create-step-2/{person_person_id}', 'InvoiceController@createStep2');
        Route::get('/invoice/create-step-3', 'InvoiceController@createStep3');
        Route::get('/invoice/no-reference/create', 'InvoiceController@createNoReference');
        Route::post('/invoice/no-reference/store', 'InvoiceController@storeNoReference');
        Route::get('/invoice/no-reference/{id}/edit', 'InvoiceController@editNoReference');
        Route::put('/invoice/no-reference/{id}', 'InvoiceController@updateNoReference');
        Route::get('/invoice/{id}/archived', 'InvoiceController@archived');
        Route::post('/invoice/send-email', 'InvoiceController@sendEmail');
        Route::resource('/invoice', 'InvoiceController');
    });

    // RETURN
    Route::any('/retur/{id}/approve', 'ReturApprovalController@approve');
    Route::any('/retur/{id}/reject', 'ReturApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/retur/{id}/archived', 'ReturController@archived');
        Route::get('/retur/request-approval', 'ReturApprovalController@requestApproval');
        Route::post('/retur/send-request-approval', 'ReturApprovalController@sendRequestApproval');
        Route::get('/retur/create-step-1', 'ReturController@createStep1');
        Route::get('/retur/create-step-2/{person_person_id}', 'ReturController@createStep2');
        Route::post('/retur/{id}/store', 'ReturController@storeRetur');
        Route::resource('/retur', 'ReturController');
    });
});
