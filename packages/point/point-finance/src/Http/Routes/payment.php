<?php

Route::group(['middleware' => 'auth', 'prefix' => 'finance/point', 'namespace' => 'Point\PointFinance\Http\Controllers'], function () {

    // Report cash or bank
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

    // Bank
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

    // Cheque
    Route::group(['namespace' => 'Cheque'], function () {
        Route::get('/cheque', 'ChequeController@index');
        Route::get('cheque/print/{id}', 'ChequeController@printCheque');
        Route::get('cheque/pending', 'ChequeController@pendingCheque');
        Route::get('cheque/list', 'ChequeController@listCheque');
        Route::get('cheque/liquid', 'ChequeController@liquid');
        Route::post('cheque/liquid', 'ChequeController@liquidProcess');
        Route::get('cheque/reject', 'ChequeController@reject');
        Route::post('cheque/reject', 'ChequeController@rejectProcess');

        // Cheque In
        Route::get('cheque/in/create/{payment_reference}', 'ChequeInController@createFromReference');
        Route::post('cheque/in/store', 'ChequeInController@storeFromReference');

        Route::get('cheque/in/create', 'ChequeInController@create');
        Route::post('cheque/in', 'ChequeInController@store');
        Route::get('cheque/in/choose-receivable', 'ChequeInController@chooseReceivable');
        Route::get('cheque/in/{id}', 'ChequeInController@show');
        Route::get('cheque/{id}/archived', 'ChequeInController@archived');
        Route::get('cheque/in/{id}/edit', 'ChequeInController@edit');
        Route::post('cheque/in/{id}', 'ChequeInController@update');

        // Cheque Out
        Route::get('cheque/out/create/{payment_reference}', 'ChequeOutController@create');
        Route::post('cheque/out', 'ChequeOutController@store');
        Route::get('cheque/out/choose-payable', 'ChequeOutController@choosePayable');
        Route::get('cheque/out/{id}', 'ChequeOutController@show');
        Route::get('cheque/{id}/archived', 'ChequeOutController@archived');
        Route::get('cheque/out/{id}/edit', 'ChequeOutController@edit');
        Route::post('cheque/out/{id}', 'ChequeOutController@update');
    });
});
