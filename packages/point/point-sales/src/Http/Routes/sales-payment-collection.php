<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {

    // PAYMENT COLLECTION
    Route::get('/payment-collection/approve-all', 'PaymentCollectionApprovalController@approveAll');
    Route::get('/payment-collection/reject-all', 'PaymentCollectionApprovalController@rejectAll');
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
        Route::post('/payment-collection/create-step-3', 'PaymentCollectionController@createStep3');
        Route::post('/payment-collection/send-email-payment', 'PaymentCollectionController@sendEmailPayment');
        Route::post('/payment-collection/{id}/edit-review', 'PaymentCollectionController@editReview');
        Route::post('/payment-collection/{id}/store', 'PaymentCollectionController@storePb');
        Route::resource('/payment-collection', 'PaymentCollectionController');
    });
});
