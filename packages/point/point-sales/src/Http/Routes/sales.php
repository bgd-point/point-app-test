<?php

Route::group(['prefix' => 'sales/point/indirect', 'namespace' => 'Point\PointSales\Http\Controllers\Sales'], function () {
    Route::get('/', function () {
        return view('point-sales::app.sales.point.sales.menu');
    });
});
