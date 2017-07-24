<?php

Route::group(['middleware' => 'auth', 'prefix' => 'sales/point', 'namespace' => 'Point\PointSales\Http\Controllers\Pos'], function () {
    Route::get('/', function () {
        return view('point-sales::app.menu.sales.point-sales');
    });

    // POS PRICING
    Route::group(['middleware' => 'auth'], function () {
        Route::post('pos/pricing/import/insert', 'PosPricingImportController@_insert');
        Route::post('pos/pricing/import/upload', 'PosPricingImportController@upload');
        Route::get('pos/pricing/import/download', 'PosPricingImportController@download');
        Route::get('pos/pricing/import/clear', 'PosPricingImportController@clear');
        Route::get('pos/pricing/import/clear-error-temp', 'PosPricingImportController@clearErrorTemp');
        Route::get('pos/pricing/import/delete/{id}', 'PosPricingImportController@delete');
        Route::post('pos/pricing/import/store', 'PosPricingImportController@store');
        Route::get('pos/pricing/import', 'PosPricingImportController@index');
        Route::get('pos/pricing/update-price', 'PosPricingController@updatePrice');
        Route::post('pos/pricing/delete', 'PosPricingController@delete');
        Route::get('pos/pricing/create-step-1', 'PosPricingController@createStep1');
        Route::get('pos/pricing/create-step-2', 'PosPricingController@createStep2');
        Route::get('pos/pricing/export', 'PosPricingController@_export');
        Route::resource('pos/pricing', 'PosPricingController');
    });
    
    // POINT OF SALES
    Route::group(['middleware' => 'auth'], function () {
        Route::get('pos/menu', function () {
            return view('point-sales::app.sales.point.pos.menu');
        });
        Route::get('pos/daily-sales/export', 'PosReportController@exportDailyReport');
        Route::get('pos/daily-sales', 'PosReportController@daily');
        Route::get('pos/sales-report/export', 'PosReportController@exportReport');
        Route::get('pos/sales-report', 'PosReportController@index');

        Route::get('pos/list-item', 'PosController@_listItem');
        Route::get('pos/clear', 'PosController@clear');
        Route::get('pos/print/{id}', 'PosController@printPos');
        Route::get('pos/{id}/archived', 'PosController@archived');
        Route::post('pos/cancel', 'PosController@cancel');
        Route::post('pos/remove_item_cart', 'PosController@removeItemCart');
        Route::get('pos/insert', 'PosController@_insert');
        Route::get('pos/add-to-chart', 'PosController@_addToCart');
        Route::resource('pos', 'PosController');
    });
});
