<?php

Route::group(['prefix' => 'finance/point', 'namespace' => 'Point\PointFinance\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('debts-aging-report', 'DebtsAgingReportController@report');
        Route::post('debts-aging-report/view', 'DebtsAgingReportController@_view');
    });
});
