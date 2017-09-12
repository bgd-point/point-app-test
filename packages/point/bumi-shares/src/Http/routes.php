<?php

Route::group(['prefix' => 'facility/bumi-shares', 'namespace' => 'Point\BumiShares\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', function () {
            return view('bumi-shares::app.facility.bumi-shares.menu');
        });

        // OWNER GROUP ROUTES
        Route::get('/owner-group/access', 'OwnerGroupController@access');
        Route::get('/owner-group/access/toggle', 'OwnerGroupController@toggleAccess');
        Route::post('/owner-group/delete', 'OwnerGroupController@delete');
        Route::resource('/owner-group', 'OwnerGroupController');

        // BROKER ROUTES
        Route::get('/broker/access', 'BrokerController@access');
        Route::get('/broker/access/toggle', 'BrokerController@toggleAccess');
        Route::post('/broker/delete', 'BrokerController@delete');
        Route::resource('/broker', 'BrokerController');

        // OWNER ROUTES
        Route::get('/owner/access', 'OwnerController@access');
        Route::get('/owner/access/toggle', 'OwnerController@toggleAccess');
        Route::post('/owner/delete', 'OwnerController@delete');
        Route::resource('/owner', 'OwnerController');

        // SHARES ROUTES
        Route::get('/shares/access', 'SharesController@access');
        Route::get('/shares/access/toggle', 'SharesController@toggleAccess');
        Route::post('/shares/delete', 'SharesController@delete');
        Route::resource('/shares', 'SharesController');
    });

    // BUY ROUTES
    Route::any('/buy/{id}/approve', 'BuyApprovalController@approve');
    Route::any('/buy/{id}/reject', 'BuyApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/buy/create-step-1', 'BuyController@createStep1');
        Route::post('/buy/create-step-2', 'BuyController@createStep2');
        Route::post('/buy/cancel', 'BuyController@cancel');
        Route::get('/buy/request-approval', 'BuyApprovalController@requestApproval');
        Route::post('/buy/send-request-approval', 'BuyApprovalController@sendRequestApproval');
        Route::get('/buy/{id}/archived', 'BuyController@archived');
        Route::resource('/buy', 'BuyController', ['except' => ['destroy']]);
    });

    // SELL ROUTES
    Route::any('/sell/{id}/approve', 'SellApprovalController@approve');
    Route::any('/sell/{id}/reject', 'SellApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/sell/create-step-1', 'SellController@createStep1');
        Route::post('/sell/create-step-2', 'SellController@createStep2');
        Route::post('/sell/cancel', 'SellController@cancel');
        Route::get('/sell/request-approval', 'SellApprovalController@requestApproval');
        Route::post('/sell/send-request-approval', 'SellApprovalController@sendRequestApproval');
        Route::get('/sell/{id}/archived', 'SellController@archived');
        Route::resource('/sell', 'SellController', ['except' => ['destroy']]);
    });

    // REPORT ROUTES
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/report/buy', 'ReportBuyControllerController@index');
        Route::get('/report/sell/export', 'ReportSellController@export');
        Route::get('/report/sell', 'ReportSellController@index');
        Route::get('/report/stock/estimate-of-selling-price', 'ReportStockController@estimateOfSellingPrice');
        Route::post('/report/stock/estimate-of-selling-price', 'ReportStockController@updateEstimateOfSellingPrice');
        Route::get('/report/stock/print', 'ReportStockController@printReport');
        Route::get('/report/stock/export', 'ReportStockController@export');
        Route::get('/report/stock/detail/{id}/{shares_id}', 'ReportStockController@detail');
        Route::get('/report/stock/detail/export/{id}/{shares_id}', 'ReportStockController@detailExport');
        Route::get('/report/stock', 'ReportStockController@index');
    });
});
