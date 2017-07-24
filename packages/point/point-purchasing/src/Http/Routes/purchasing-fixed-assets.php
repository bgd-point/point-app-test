<?php

Route::group(['prefix' => 'purchasing/point/fixed-assets', 'namespace' => 'Point\PointPurchasing\Http\Controllers\FixedAssets'], function () {
    Route::get('/', function() {
        return view('point-purchasing::app.purchasing.point.fixed-assets.menu');
    }); 

    // PURCHASE REQUISITION
    Route::get('/purchase-requisition/reject-all', 'FixedAssetsPurchaseRequisitionApprovalController@rejectAll');
    Route::get('/purchase-requisition/approve-all', 'FixedAssetsPurchaseRequisitionApprovalController@approveAll');
    Route::any('/purchase-requisition/{id}/approve', 'FixedAssetsPurchaseRequisitionApprovalController@approve');
    Route::any('/purchase-requisition/{id}/reject', 'FixedAssetsPurchaseRequisitionApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/purchase-requisition/vesa-approval', 'PurchaseRequisitionVesaController@approval');
        Route::get('/purchase-requisition/vesa-rejected', 'PurchaseRequisitionVesaController@rejected');
        Route::get('/purchase-requisition/vesa-create-purchase-order', 'PurchaseRequisitionVesaController@createPurchaseOrder');

        Route::get('/purchase-requisition/request-approval', 'FixedAssetsPurchaseRequisitionApprovalController@requestApproval');
        Route::post('/purchase-requisition/send-request-approval', 'FixedAssetsPurchaseRequisitionApprovalController@sendRequestApproval');
        Route::get('/purchase-requisition/{id}/archived', 'FixedAssetsPurchaseRequisitionController@archived');
        Route::resource('/purchase-requisition', 'FixedAssetsPurchaseRequisitionController');
    });

    // PURCHASE ORDER
    Route::get('/purchase-order/reject-all', 'FixedAssetsPurchaseOrderApprovalController@rejectAll');
    Route::get('/purchase-order/approve-all', 'FixedAssetsPurchaseOrderApprovalController@approveAll');
    Route::any('/purchase-order/{id}/approve', 'FixedAssetsPurchaseOrderApprovalController@approve');
    Route::any('/purchase-order/{id}/reject', 'FixedAssetsPurchaseOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/purchase-order/vesa-approval', 'FixedAssetsPurchaseOrderVesaController@approval');
        Route::get('/purchase-order/vesa-rejected', 'FixedAssetsPurchaseOrderVesaController@rejected');
        Route::get('/purchase-order/vesa-create-downpayment', 'FixedAssetsPurchaseOrderVesaController@createDowpayment');

        Route::get('/purchase-order/request-approval', 'FixedAssetsPurchaseOrderApprovalController@requestApproval');
        Route::post('/purchase-order/send-request-approval', 'FixedAssetsPurchaseOrderApprovalController@sendRequestApproval');
        Route::get('/purchase-order/create-step-1', 'FixedAssetsPurchaseOrderController@createStep1');
        Route::get('/purchase-order/create-step-2/{id}', 'FixedAssetsPurchaseOrderController@createStep2');
        Route::get('/purchase-order/{id}/archived', 'FixedAssetsPurchaseOrderController@archived');
        Route::post('/purchase-order/{id}/store', 'FixedAssetsPurchaseOrderController@store');
        Route::get('/purchase-order/basic/{id}/edit', 'FixedAssetsPurchaseOrderController@editBasic');
        Route::post('/purchase-order/basic/store', 'FixedAssetsPurchaseOrderController@store');
        Route::get('/purchase-order/basic/create', 'FixedAssetsPurchaseOrderController@create');
        Route::resource('/purchase-order', 'FixedAssetsPurchaseOrderController');
    });

    // DOWNPAYMENT
    Route::get('/downpayment/reject-all', 'FixedAssetsDownpaymentApprovalController@rejectAll');
    Route::get('/downpayment/approve-all', 'FixedAssetsDownpaymentApprovalController@approveAll');
    Route::any('/downpayment/{id}/approve', 'FixedAssetsDownpaymentApprovalController@approve');
    Route::any('/downpayment/{id}/reject', 'FixedAssetsDownpaymentApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/downpayment/vesa-approval', 'FixedAssetsDownpaymentVesaController@approval');
        Route::get('/downpayment/vesa-rejected', 'FixedAssetsDownpaymentVesaController@rejected');
        Route::get('/downpayment/vesa-create-payment', 'FixedAssetsDownpaymentVesaController@createPayment');

        Route::get('/downpayment/request-approval', 'FixedAssetsDownpaymentApprovalController@requestApproval');
        Route::post('/downpayment/send-request-approval', 'FixedAssetsDownpaymentApprovalController@sendRequestApproval');
        Route::get('/downpayment/{id}/archived', 'FixedAssetsDownpaymentController@archived');
        Route::get('/downpayment/create/{id?}', 'FixedAssetsDownpaymentController@create');
        Route::resource('/downpayment', 'FixedAssetsDownpaymentController');
    });

    // GOODS RECEIVED
    Route::get('/goods-received/reject-all', 'FixedAssetsGoodsReceivedApprovalController@rejectAll');
    Route::get('/goods-received/approve-all', 'FixedAssetsGoodsReceivedApprovalController@approveAll');
    Route::any('/goods-received/{id}/approve', 'FixedAssetsGoodsReceivedApprovalController@approve');
    Route::any('/goods-received/{id}/reject', 'FixedAssetsGoodsReceivedApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/goods-received/vesa-approval', 'FixedAssetsGoodsReceivedVesaController@approval');
        Route::get('/goods-received/vesa-create', 'FixedAssetsGoodsReceivedVesaController@create');
        Route::get('/goods-received/vesa-rejected', 'FixedAssetsGoodsReceivedVesaController@rejected');

        Route::get('/goods-received/request-approval', 'FixedAssetsGoodsReceivedApprovalController@requestApproval');
        Route::post('/goods-received/send-request-approval', 'FixedAssetsGoodsReceivedApprovalController@sendRequestApproval');
        Route::get('/goods-received/{id}/archived', 'FixedAssetsGoodsReceivedController@archived');
        Route::get('/goods-received/create-step-1', 'FixedAssetsGoodsReceivedController@createStep1');
        Route::get('/goods-received/create-step-2/{purchase_order_id}/{expedition_id?}', 'FixedAssetsGoodsReceivedController@createStep2');
        Route::resource('/goods-received', 'FixedAssetsGoodsReceivedController');
    });

    // INVOICE
    Route::get('/invoice/reject-all', 'FixedAssetsInvoiceApprovalController@rejectAll');
    Route::get('/invoice/approve-all', 'FixedAssetsInvoiceApprovalController@approveAll');
    Route::any('/invoice/{id}/approve', 'FixedAssetsInvoiceApprovalController@approve');
    Route::any('/invoice/{id}/reject', 'FixedAssetsInvoiceApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/invoice/vesa-create', 'InvoiceVesaController@create');
        // AJAX GETTING ITEM UNIT
        Route::get('/invoice/basic/unit', 'Basic\FixedAssetsInvoiceController@_unit');
        Route::get('/invoice/basic/create', 'Basic\FixedAssetsInvoiceController@create');
        Route::post('/invoice/basic/store', 'Basic\FixedAssetsInvoiceController@store');
        Route::get('/invoice/basic/{id}/edit', 'Basic\FixedAssetsInvoiceController@edit');
        Route::put('/invoice/basic/{id}', 'Basic\FixedAssetsInvoiceController@update');

        Route::get('/invoice/{id}/archived', 'FixedAssetsInvoiceController@archived');
        Route::get('/invoice/list', 'FixedAssetsInvoiceController@_list');
        Route::get('/invoice/search', 'FixedAssetsInvoiceController@_search');
        Route::get('/invoice/create-step-1', 'FixedAssetsInvoiceController@createStep1');
        Route::get('/invoice/create-step-2/{person_supplier_id}', 'FixedAssetsInvoiceController@createStep2');
        Route::get('/invoice/create-step-3', 'FixedAssetsInvoiceController@createStep3');
        Route::post('/invoice/{id}/store', 'FixedAssetsInvoiceController@storeFb');
        Route::resource('/invoice', 'FixedAssetsInvoiceController');
    });
    
    // CONTRACT
    Route::get('/contract/reject-all', 'FixedAssetsContractApprovalController@rejectAll');
    Route::get('/contract/approve-all', 'FixedAssetsContractApprovalController@approveAll');
    Route::any('/contract/{id}/approve', 'FixedAssetsContractApprovalController@approve');
    Route::any('/contract/{id}/reject', 'FixedAssetsContractApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/contract/vesa-create', 'ContractVesaController@create');
        Route::get('/contract/request-approval', 'FixedAssetsContractApprovalController@requestApproval');
        Route::post('/contract/send-request-approval', 'FixedAssetsContractApprovalController@sendRequestApproval');
        
        Route::post('/contract/join/store', 'FixedAssetsContractController@storeJoin');
        Route::get('/contract/join/get-detail-contract', 'FixedAssetsContractController@_getDetailContract');
        Route::get('/contract/join/{contract_reference_id}', 'FixedAssetsContractController@createJoin');
        Route::get('/contract/{id}/archived', 'FixedAssetsContractController@archived');
        Route::get('/contract/create-step-1', 'FixedAssetsContractController@createStep1');
        Route::get('/contract/create-step-2/{contract_reference_id}', 'FixedAssetsContractController@createStep2');
        Route::post('/contract/create-step-3', 'FixedAssetsContractController@createStep3');
        Route::post('/contract/{id}/store', 'FixedAssetsContractController@storeFb');
        Route::resource('/contract', 'FixedAssetsContractController');
    });

    // RETURN
    Route::any('/retur/{id}/approve', 'ReturApprovalController@approve');
    Route::any('/retur/{id}/reject', 'ReturApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/retur/vesa-approval', 'ReturVesaController@approval');
        Route::get('/retur/vesa-rejected', 'ReturVesaController@rejected');
        Route::get('/retur/vesa-create', 'ReturVesaController@create');

        Route::get('/retur/{id}/archived', 'ReturController@archived');
        Route::get('/retur/request-approval', 'ReturApprovalController@requestApproval');
        Route::post('/retur/send-request-approval', 'ReturApprovalController@sendRequestApproval');
        Route::get('/retur/create-step-1', 'ReturController@createStep1');
        Route::get('/retur/create-step-2/{person_supplier_id}', 'ReturController@createStep2');
        Route::post('/retur/{id}/store', 'ReturController@storeRetur');
        Route::resource('/retur', 'ReturController');
    });

    // PAYMENT ORDER
    Route::get('/payment-order/reject-all', 'FixedAssetsPaymentOrderApprovalController@rejectAll');
    Route::get('/payment-order/approve-all', 'FixedAssetsPaymentOrderApprovalController@approveAll');
    Route::any('/payment-order/{id}/approve', 'FixedAssetsPaymentOrderApprovalController@approve');
    Route::any('/payment-order/{id}/reject', 'FixedAssetsPaymentOrderApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/payment-order/vesa-approval', 'FixedAssetsPaymentOrderVesaController@approval');
        Route::get('/payment-order/vesa-rejected', 'FixedAssetsPaymentOrderVesaController@rejected');
        Route::get('/payment-order/vesa-create', 'FixedAssetsPaymentOrderVesaController@create');
        Route::get('/payment-order/request-approval', 'FixedAssetsPaymentOrderApprovalController@requestApproval');
        Route::post('/payment-order/send-request-approval', 'FixedAssetsPaymentOrderApprovalController@sendRequestApproval');
        Route::get('/payment-order/{id}/archived', 'FixedAssetsPaymentOrderController@archived');
        Route::get('/payment-order/create-step-1', 'FixedAssetsPaymentOrderController@createStep1');
        Route::get('/payment-order/create-step-2/{person_supplier_id}', 'FixedAssetsPaymentOrderController@createStep2');
        Route::post('/payment-order/create-step-3', 'FixedAssetsPaymentOrderController@createStep3');
        Route::post('/payment-order/{id}/edit-review', 'FixedAssetsPaymentOrderController@editReview');
        Route::post('/payment-order/{id}/store', 'FixedAssetsPaymentOrderController@storePb');
        Route::resource('/payment-order', 'FixedAssetsPaymentOrderController');
    });
});
