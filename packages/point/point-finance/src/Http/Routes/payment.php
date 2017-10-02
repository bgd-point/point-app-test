<?php

Route::group(['middleware' => 'auth', 'prefix' => 'finance/point', 'namespace' => 'Point\PointFinance\Http\Controllers'], function () {

    // Report cash or bank
    Route::get('report/export/pdf', 'ReportController@exportPDF');
    Route::get('report/export', 'ReportController@export');
    Route::post('report/view', 'ReportController@_view');
    Route::get('report/{type}', 'ReportController@index');

    // Payment
    Route::get('payment/choose/{formulir_id}', 'PaymentController@choose');
    Route::post('payment/cancel', 'PaymentController@cancel');
    Route::get('payment/vesa-create', 'PaymentVesaController@create');

    // Cash
    Route::group(['namespace' => 'Cash'], function () {
        Route::get('/cash', 'CashController@index');
        Route::get('cash/print/{id}', 'CashController@printCash');

        // Cash In
        Route::get('cash/in/create/{payment_reference}', 'CashInController@createFromReference');
        Route::post('cash/in/store', 'CashInController@storeFromReference');

        Route::get('cash/in/create', 'CashInController@create');
        Route::post('cash/in', 'CashInController@store');
        Route::get('cash/in/choose-receivable', 'CashInController@chooseReceivable');
        Route::get('cash/in/{id}', 'CashInController@show');
        Route::get('cash/{id}/archived', 'CashInController@archived');
        Route::get('cash/in/{id}/edit', 'CashInController@edit');
        Route::post('cash/in/{id}', 'CashInController@update');

        // Cash Out
        Route::get('cash/out/create/{payment_reference}', 'CashOutController@create');
        Route::post('cash/out', 'CashOutController@store');
        Route::get('cash/out/choose-payable', 'CashOutController@choosePayable');
        Route::get('cash/out/{id}', 'CashOutController@show');
        Route::get('cash/{id}/archived', 'CashOutController@archived');
        Route::get('cash/out/{id}/edit', 'CashOutController@edit');
        Route::post('cash/out/{id}', 'CashOutController@update');
    });

    // Cash
    Route::group(['namespace' => 'Bank'], function () {
        Route::get('/bank', 'BankController@index');
        Route::get('bank/print/{id}', 'BankController@printBank');

        // Bank In
        Route::get('bank/in/create/{payment_reference}', 'BankInController@createFromReference');
        Route::post('bank/in/store', 'BankInController@storeFromReference');

        Route::get('bank/in/create', 'BankInController@create');
        Route::post('bank/in', 'BankInController@store');
        Route::get('bank/in/choose-receivable', 'BankInController@chooseReceivable');
        Route::get('bank/in/{id}', 'BankInController@show');
        Route::get('bank/{id}/archived', 'BankInController@archived');
        Route::get('bank/in/{id}/edit', 'BankInController@edit');
        Route::post('bank/in/{id}', 'BankInController@update');

        // Bank Out
        Route::get('bank/out/create/{payment_reference}', 'BankOutController@create');
        Route::post('bank/out', 'BankOutController@store');
        Route::get('bank/out/choose-payable', 'BankOutController@choosePayable');
        Route::get('bank/out/{id}', 'BankOutController@show');
        Route::get('bank/{id}/archived', 'BankOutController@archived');
        Route::get('bank/out/{id}/edit', 'BankOutController@edit');
        Route::post('bank/out/{id}', 'BankOutController@update');
    });
});
