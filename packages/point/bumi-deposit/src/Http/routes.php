<?php

Route::group(['prefix' => 'facility/bumi-deposit', 'namespace' => 'Point\BumiDeposit\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', 'DepositMenuController@index');

        Route::get('/deposit/{id}/archived', 'DepositController@archived');
        Route::get('/deposit/{id}/withdraw', 'DepositController@withdraw');
        Route::post('/deposit/{id}/store-withdraw', 'DepositController@storeWithdraw');
        Route::get('/deposit/{id}/extend', 'DepositController@extend');
        Route::post('/deposit/{id}/store-extend', 'DepositController@storeExtend');
        Route::resource('/deposit', 'DepositController');

        // DEPOSIT CATEGORY
        Route::post('/category/delete', 'DepositCategoryController@delete');
        Route::resource('/category', 'DepositCategoryController');

        // DEPOSIT BANK
        Route::post('/bank/delete', 'BankController@delete');
        Route::get('/bank/select', 'BankController@_select');
        Route::resource('/bank', 'BankController');

        // DEPOSIT OWNER
        Route::post('/owner/delete', 'DepositOwnerController@delete');
        Route::resource('/owner', 'DepositOwnerController');

        // DEPOSIT GROUP
        Route::post('/group/delete', 'DepositGroupController@delete');
        Route::resource('/group', 'DepositGroupController');

        // DEPOSIT GROUP REPORT
        Route::get('/deposit-report', 'DepositReportController@index');
        Route::get('/deposit-report/excel', 'DepositReportController@excel');
    });
});
