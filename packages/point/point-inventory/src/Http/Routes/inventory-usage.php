<?php

Route::group(['prefix' => 'inventory/point', 'namespace' => 'Point\PointInventory\Http\Controllers\InventoryUsage'], function () {
    Route::get('inventory-usage/reject-all', 'InventoryUsageApprovalController@rejectAll');
    Route::get('inventory-usage/approve-all', 'InventoryUsageApprovalController@approveAll');
    Route::any('inventory-usage/{id}/approve', 'InventoryUsageApprovalController@approve');
    Route::any('inventory-usage/{id}/reject', 'InventoryUsageApprovalController@reject');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('inventory-usage/vesa-approval', 'InventoryUsageVesaController@approval');
        Route::get('inventory-usage/vesa-rejected', 'InventoryUsageVesaController@rejected');
        Route::get('inventory-usage/request-approval', 'InventoryUsageApprovalController@requestApproval');
        Route::post('inventory-usage/send-request-approval', 'InventoryUsageApprovalController@sendRequestApproval');

        // Item Quantity Ajax Request
        Route::get('inventory-usage/{id}/export', 'InventoryUsageController@exportPDF');
        Route::get('inventory-usage/get-item', 'InventoryUsageController@_getItemHasAvailableStock');
        Route::get('inventory-usage/quantity', 'InventoryUsageController@_quantity');
        Route::get('inventory-usage/{id}/archived', 'InventoryUsageController@archived');
        Route::resource('inventory-usage', 'InventoryUsageController');
    });
});
