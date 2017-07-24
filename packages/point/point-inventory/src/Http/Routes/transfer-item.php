<?php

Route::group(['prefix' => 'inventory/point','namespace' => 'Point\PointInventory\Http\Controllers\TransferItem'], function () {
    Route::get('transfer-item/reject-all', 'TransferItemApprovalController@rejectAll');
    Route::get('transfer-item/approve-all', 'TransferItemApprovalController@approveAll');
    Route::any('transfer-item/send/{id}/approve', 'TransferItemApprovalController@approve');
    Route::any('transfer-item/send/{id}/reject', 'TransferItemApprovalController@reject');

    Route::group(['prefix' => 'transfer-item', 'middleware' => 'auth'], function () {
        Route::get('send/vesa-approval', 'TransferItemVesaController@approval');
        Route::get('send/vesa-rejected', 'TransferItemVesaController@rejected');
        Route::get('send/vesa-create-receive', 'TransferItemVesaController@receive');
        Route::get('/', function () {
            return view('point-inventory::app.inventory.point.transfer-item.index');
        });
        Route::get('/', 'TransferItemController@index');
        // Send Item

        Route::get('send/{id}/archived', 'TransferItemController@archived');
        Route::get('send/request-approval', 'TransferItemApprovalController@requestApproval');
        Route::post('send/send-request-approval', 'TransferItemApprovalController@sendRequestApproval');
        Route::resource('send', 'TransferItemController');

        // Receive Item
        Route::get('/received', 'ReceiveItemController@index');
        Route::get('/received/create/{id}', 'ReceiveItemController@create');
        Route::post('/received/store/{id}', 'ReceiveItemController@store');
    });
});
