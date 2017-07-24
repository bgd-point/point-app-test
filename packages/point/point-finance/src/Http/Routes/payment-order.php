<?php

Route::group(['prefix' => 'finance/point', 'namespace' => 'Point\PointFinance\Http\Controllers\PaymentOrder'], function () {
    Route::any('/payment-order/{id}/approve', 'PaymentOrderApprovalController@approve');
    Route::any('/payment-order/{id}/reject', 'PaymentOrderApprovalController@reject');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('payment-order/vesa-approval', 'PaymentOrderVesaController@approval');
        Route::get('payment-order/vesa-rejected', 'PaymentOrderVesaController@rejected');
        Route::get('payment-order/request-approval', 'PaymentOrderApprovalController@requestApproval');
        Route::post('payment-order/send-request-approval', 'PaymentOrderApprovalController@sendRequestApproval');
        Route::get('payment-order/access', 'PaymentOrderController@access');
        Route::get('payment-order/create-step-1', 'PaymentOrderController@createStep1');
        Route::post('payment-order/create-step-2', 'PaymentOrderController@createStep2');
        Route::post('payment-order/create-step-3', 'PaymentOrderController@createStep3');
        Route::post('payment-order/{id}/edit-review', 'PaymentOrderController@editReview');
        Route::post('payment-order/{id}/store', 'PaymentOrderController@storePb');
        Route::get('payment-order/{id}/archived', 'PaymentOrderController@archived');
        Route::post('payment-order/update/{id}', 'PaymentOrderController@update');
        Route::post('payment-order/cancel', 'PaymentOrderController@cancel');
        Route::resource('payment-order', 'PaymentOrderController');
    });
});
