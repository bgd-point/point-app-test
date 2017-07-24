<?php

Route::group(['middleware' => 'auth', 'prefix' => 'accounting', 'namespace' => 'Point\Framework\Http\Controllers\Accounting'], function () {
    Route::get('/balance-sheet/export', 'BalanceSheetController@export');
    Route::get('/balance-sheet', 'BalanceSheetController@index');
    Route::get('/trial-balance/export', 'TrialBalanceController@export');
    Route::get('/trial-balance', 'TrialBalanceController@index');
    Route::get('/cashflow/export', 'CashFlowController@export');
    Route::get('/cashflow', 'CashFlowController@index');
    Route::get('/general-ledger/export', 'GeneralLedgerController@export');
    Route::get('/general-ledger', 'GeneralLedgerController@index');
    Route::get('/sub-ledger/export', 'SubLedgerController@export');
    Route::get('/sub-ledger', 'SubLedgerController@index');
    Route::get('/sub-ledger/coa', 'SubLedgerController@_coa');
    Route::get('/profit-and-loss/export', 'ProfitAndLossController@export');
    Route::get('/profit-and-loss', 'ProfitAndLossController@index');
});
