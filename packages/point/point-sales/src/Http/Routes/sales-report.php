<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {
    Route::get('/report/pdf', 'SalesReportController@indexPDF');
    Route::get('/report/export', 'SalesReportController@export');
    Route::get('/report', 'SalesReportController@index');

    Route::get('/retur-report', 'SalesReturReportController@index');
});
