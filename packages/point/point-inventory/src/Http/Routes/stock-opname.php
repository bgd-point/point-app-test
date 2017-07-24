<?php

Route::group(['prefix' => 'inventory/point', 'namespace' => 'Point\PointInventory\Http\Controllers\StockOpname'], function () {
    Route::get('stock-opname/reject-all', 'StockOpnameApprovalController@rejectAll');
    Route::get('stock-opname/approve-all', 'StockOpnameApprovalController@approveAll');
    Route::any('stock-opname/{id}/approve', 'StockOpnameApprovalController@approve');
    Route::any('stock-opname/{id}/reject', 'StockOpnameApprovalController@reject');
    
    Route::group(['middleware' => 'auth'], function () {
        Route::get('stock-opname/vesa-approval', 'StockOpnameVesaController@approval');
        Route::get('stock-opname/vesa-rejected', 'StockOpnameVesaController@rejected');
        Route::get('stock-opname/request-approval', 'StockOpnameApprovalController@requestApproval');
        Route::post('stock-opname/send-request-approval', 'StockOpnameApprovalController@sendRequestApproval');
        
        Route::get('stock-opname/{id}/archived', 'StockOpnameController@archived');
        Route::get('stock-opname/clear-temp/{id}', 'StockOpnameController@clearTemp');

        Route::resource('stock-opname', 'StockOpnameController');
    });
});
