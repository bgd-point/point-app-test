<?php
Route::group(['prefix' => 'accounting/point/cut-off', 'namespace' => 'Point\PointAccounting\Http\Controllers\Cutoff'], function() {
	Route::get('/', function() {
        return view('point-accounting::app.accounting.point.cut-off.menu');
    }); 

	# cut off account routes
	Route::any('/account/{id}/approve', 'CutOffAccountApprovalController@approve');
	Route::any('/account/{id}/reject', 'CutOffAccountApprovalController@reject');
	Route::group(['middleware' => 'auth'], function() {
		Route::get('account/vesa-rejected', 'CutOffAccountVesaController@rejected');
        Route::get('account/vesa-approval', 'CutOffAccountVesaController@approval');
        
		Route::get('account/request-approval', 'CutOffAccountApprovalController@requestApproval');
		Route::post('account/send-request-approval', 'CutOffAccountApprovalController@sendRequestApproval');
		Route::get('account/clear-tmp', 'CutOffAccountController@clearTmp');
		Route::post('account/store-tmp-details', 'CutOffAccountController@_storeTmp');
		Route::post('account/cancel', 'CutOffAccountController@cancel');
		Route::get('account/{id}/archived', 'CutOffAccountController@archived');

		Route::resource('account', 'CutOffAccountController');

	});

	# cut off inventory routes
	Route::any('/inventory/{id}/approve', 'CutOffInventoryApprovalController@approve');
	Route::any('/inventory/{id}/reject', 'CutOffInventoryApprovalController@reject');
	Route::group(['middleware' => 'auth'], function() {
		Route::get('inventory/vesa-rejected', 'CutOffInvetoryVesaController@rejected');
        Route::get('inventory/vesa-approval', 'CutOffInvetoryVesaController@approval');
		Route::get('inventory/request-approval', 'CutOffInventoryApprovalController@requestApproval');
		Route::post('inventory/send-request-approval', 'CutOffInventoryApprovalController@sendRequestApproval');

		Route::get('inventory/clear-tmp', 'CutOffInventoryController@clearTmp');
		Route::post('inventory/clear-tmp-detail', 'CutOffInventoryController@_clearTmpDetail');
		Route::post('inventory/delete-tmp', 'CutOffInventoryController@_deleteTmp');
		Route::post('inventory/store-tmp', 'CutOffInventoryController@_storeTmp');
		Route::get('inventory/load-details', 'CutOffInventoryController@_loadDetails');
		Route::get('inventory/{id}/archived', 'CutOffInventoryController@archived');
		Route::post('inventory/cancel', 'CutOffInventoryController@cancel');
		Route::get('inventory/load-details-account-inventory', 'CutOffInventoryController@_loadDetailsAccountInventory');

		Route::resource('inventory', 'CutOffInventoryController');

	});

	# cut off payable routes
	Route::any('/payable/{id}/approve', 'CutOffPayableApprovalController@approve');
	Route::any('/payable/{id}/reject', 'CutOffPayableApprovalController@reject');
	Route::group(['middleware' => 'auth'], function() {
		Route::get('payable/vesa-rejected', 'CutOffPayableVesaController@rejected');
        Route::get('payable/vesa-approval', 'CutOffPayableVesaController@approval');
		Route::get('payable/request-approval', 'CutOffPayableApprovalController@requestApproval');
		Route::post('payable/send-request-approval', 'CutOffPayableApprovalController@sendRequestApproval');

		Route::get('payable/clear-tmp', 'CutOffPayableController@clearTmp');
		Route::post('payable/clear-tmp-detail', 'CutOffPayableController@_clearTmpDetail');
		Route::post('payable/delete-tmp', 'CutOffPayableController@_deleteTmp');
		Route::post('payable/store-tmp-details', 'CutOffPayableController@_storeTmpDetails');
		Route::post('payable/store-tmp', 'CutOffPayableController@_storeTmp');
		Route::get('payable/load-details', 'CutOffPayableController@_loadDetails');
		Route::get('payable/{id}/archived', 'CutOffPayableController@archived');
		Route::post('payable/cancel', 'CutOffPayableController@cancel');
		Route::get('payable/load-details-account-payable', 'CutOffPayableController@_loadDetailsAccountPayable');

		Route::resource('payable', 'CutOffPayableController');

	});

	# cut off receivable routes
	Route::any('/receivable/{id}/approve', 'CutOffReceivableApprovalController@approve');
	Route::any('/receivable/{id}/reject', 'CutOffReceivableApprovalController@reject');
	Route::group(['middleware' => 'auth'], function() {
		Route::get('receivable/vesa-rejected', 'CutOffReceivableVesaController@rejected');
        Route::get('receivable/vesa-approval', 'CutOffReceivableVesaController@approval');
		Route::get('receivable/request-approval', 'CutOffReceivableApprovalController@requestApproval');
		Route::post('receivable/send-request-approval', 'CutOffReceivableApprovalController@sendRequestApproval');

		Route::get('receivable/clear-tmp', 'CutOffReceivableController@clearTmp');
		Route::post('receivable/clear-tmp-detail', 'CutOffReceivableController@_clearTmpDetail');
		Route::post('receivable/delete-tmp', 'CutOffReceivableController@_deleteTmp');
		Route::post('receivable/store-tmp-details', 'CutOffReceivableController@_storeTmpDetails');
		Route::post('receivable/store-tmp', 'CutOffReceivableController@_storeTmp');
		Route::get('receivable/load-details', 'CutOffReceivableController@_loadDetails');
		Route::get('receivable/{id}/archived', 'CutOffReceivableController@archived');
		Route::post('receivable/cancel', 'CutOffReceivableController@cancel');
		Route::get('receivable/load-details-account-receivable', 'CutOffReceivableController@_loadDetailsAccountReceivable');

		Route::resource('receivable', 'CutOffReceivableController');

	});

	# cut off fixed assets routes
		Route::any('/fixed-assets/{id}/approve', 'CutOffFixedAssetsApprovalController@approve');
		Route::any('/fixed-assets/{id}/reject', 'CutOffFixedAssetsApprovalController@reject');
		Route::group(['middleware' => 'auth'], function() {
			Route::get('fixed-assets/vesa-rejected', 'CutOffFixedAssetsVesaController@rejected');	
       		Route::get('fixed-assets/vesa-approval', 'CutOffFixedAssetsVesaController@approval');
			Route::get('fixed-assets/request-approval', 'CutOffFixedAssetsApprovalController@requestApproval');
			Route::post('fixed-assets/send-request-approval', 'CutOffFixedAssetsApprovalController@sendRequestApproval');

			Route::get('fixed-assets/clear-tmp', 'CutOffFixedAssetsController@clearTmp');
			Route::post('fixed-assets/clear-tmp-detail', 'CutOffFixedAssetsController@_clearTmpDetail');
			Route::post('fixed-assets/delete-tmp', 'CutOffFixedAssetsController@_deleteTmp');
			Route::post('fixed-assets/store-tmp-details', 'CutOffFixedAssetsController@_storeTmpDetails');
			Route::post('fixed-assets/store-tmp', 'CutOffFixedAssetsController@_storeTmp');
			Route::get('fixed-assets/load-details', 'CutOffFixedAssetsController@_loadDetails');
			Route::get('fixed-assets/{id}/archived', 'CutOffFixedAssetsController@archived');
			Route::post('fixed-assets/cancel', 'CutOffFixedAssetsController@cancel');
			Route::get('fixed-assets/load-details-account-fixed-assets', 'CutOffFixedAssetsController@_loadDetailsAccountFixedAssets');

			Route::resource('fixed-assets', 'CutOffFixedAssetsController');

		});
});
