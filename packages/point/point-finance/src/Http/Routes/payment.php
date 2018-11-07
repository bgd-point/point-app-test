<?php

Route::group(['prefix' => 'finance/point', 'namespace' => 'Point\PointFinance\Http\Controllers'], function () {

    Route::any('/cash-advance/{id}/approve', 'CashAdvanceApprovalController@approve');
    Route::any('/cash-advance/{id}/reject', 'CashAdvanceApprovalController@reject');
    Route::get('/cash-advance/reject-all', 'CashAdvanceApprovalController@rejectAll');
    Route::get('/cash-advance/approve-all', 'CashAdvanceApprovalController@approveAll');
    Route::group(['middleware' => 'auth'], function () {
        // Report cash or bank
        Route::get('report/export/pdf', 'ReportController@exportPDF');
        Route::get('report/export', 'ReportController@export');
        Route::post('report/view', 'ReportController@_view');
        Route::get('report/{type}', 'ReportController@index');

        // Report cash or bank
        Route::group(['namespace' => 'DebtCash'], function () {
            Route::get('debt-report/export/pdf', 'ReportController@exportPDF');
            Route::get('debt-report/export', 'ReportController@export');
            Route::post('debt-report/view', 'ReportController@_view');
            Route::get('debt-report/{type}', 'ReportController@index');
        });
        // Allocation Report
        Route::get('allocation-report', 'AllocationReportController@index');
        Route::get('allocation-report/export', 'AllocationReportController@export');

        // Payment
        Route::get('payment/choose/{formulir_id}', 'PaymentController@choose');
        Route::post('payment/cancel', 'PaymentController@cancel');
        Route::get('payment/vesa-create', 'PaymentVesaController@create');

        // Cash Advance
        Route::get('cash-advance/vesa-approval', 'CashAdvanceVesaController@approval');
        Route::get('cash-advance/vesa-rejected', 'CashAdvanceVesaController@rejected');
        Route::get('cash-advance/request-approval', 'CashAdvanceApprovalController@requestApproval');
        Route::post('cash-advance/send-request-approval', 'CashAdvanceApprovalController@sendRequestApproval');
        Route::get('cash-advance/list', 'CashAdvanceController@_list');
        Route::get('cash-advance/{id}/archived', 'CashAdvanceController@archived');
        Route::get('cash-advance/{id}/hand-over', 'CashAdvanceController@handOver');
        Route::resource('/cash-advance', 'CashAdvanceController');

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
        Route::group(['namespace' => 'DebtCash'], function () {
            Route::get('/debt-cash', 'CashController@index');
            Route::get('debt-cash/print/{id}', 'CashController@printCash');

            // Cash In
            Route::get('debt-cash/in/create/{payment_reference}', 'CashInController@createFromReference');
            Route::post('debt-cash/in/store', 'CashInController@storeFromReference');

            Route::get('debt-cash/in/create', 'CashInController@create');
            Route::post('debt-cash/in', 'CashInController@store');
            Route::get('debt-cash/in/choose-receivable', 'CashInController@chooseReceivable');
            Route::get('debt-cash/in/{id}', 'CashInController@show');
            Route::get('debt-cash/{id}/archived', 'CashInController@archived');
            Route::get('debt-cash/in/{id}/edit', 'CashInController@edit');
            Route::post('debt-cash/in/{id}', 'CashInController@update');

            // Cash Out
            Route::get('debt-cash/out/create/{payment_reference}', 'CashOutController@create');
            Route::post('debt-cash/out', 'CashOutController@store');
            Route::get('debt-cash/out/choose-payable', 'CashOutController@choosePayable');
            Route::get('debt-cash/out/{id}', 'CashOutController@show');
            Route::get('debt-cash/{id}/archived', 'CashOutController@archived');
            Route::get('debt-cash/out/{id}/edit', 'CashOutController@edit');
            Route::post('debt-cash/out/{id}', 'CashOutController@update');
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

        // additional route to setApprovalTo and sending email form cancel request
        Route::post('payment/request-cancel', 'PaymentController@requestCancel');
    });
});
