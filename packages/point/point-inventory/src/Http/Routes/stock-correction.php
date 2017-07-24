<?php

Route::group(['prefix' => 'inventory/point', 'namespace' => 'Point\PointInventory\Http\Controllers\StockCorrection'], function () {
    Route::get('stock-correction/reject-all', 'StockCorrectionApprovalController@rejectAll');
    Route::get('stock-correction/approve-all', 'StockCorrectionApprovalController@approveAll');
    Route::any('stock-correction/{id}/approve', 'StockCorrectionApprovalController@approve');
    Route::any('stock-correction/{id}/reject', 'StockCorrectionApprovalController@reject');
    
    Route::group(['middleware' => 'auth'], function () {
        Route::get('stock-correction/vesa-approval', 'StockCorrectionVesaController@approval');
        Route::get('stock-correction/vesa-rejected', 'StockCorrectionVesaController@rejected');
        Route::get('stock-correction/request-approval', 'StockCorrectionApprovalController@requestApproval');
        Route::post('stock-correction/send-request-approval', 'StockCorrectionApprovalController@sendRequestApproval');

        // Item Quantity Ajax Request
        Route::get('stock-correction/get-item', 'StockCorrectionController@_getItemHasAvailableStock');
        Route::get('stock-correction/quantity', 'StockCorrectionController@_quantity');
        Route::get('stock-correction/{id}/archived', 'StockCorrectionController@archived');
        Route::resource('stock-correction', 'StockCorrectionController');
    });
});
