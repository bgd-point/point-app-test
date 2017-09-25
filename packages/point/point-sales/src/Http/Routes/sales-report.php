<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {
	Route::get('/report/export', 'SalesReportController@export');
	Route::get('/report', 'SalesReportController@index');
});