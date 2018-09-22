<?php
Route::group(['middleware' => 'auth', 'prefix' => 'master', 'namespace' => 'Point\Framework\Http\Controllers\Master'], function () {

    // Vesa
    Route::get('/coa/vesa-setting-journal', 'MasterVesaController@settingJournal');
    Route::get('/item/stock-reminder', 'MasterVesaController@stockReminder');

    // Coa
    Route::group(['namespace' => 'Account'], function () {
        Route::post('coa/group/store', 'CoaGroupController@_store');
        Route::get('coa/group/show', 'CoaGroupController@_show');
        Route::get('coa/group/edit', 'CoaGroupController@_edit');
        Route::post('coa/group/update', 'CoaGroupController@_update');
        Route::post('coa/group/{id}/delete', 'CoaGroupController@delete');

        Route::get('coa/depreciation/show', 'AccountDepreciationController@show');
        Route::post('coa/depreciation', 'AccountDepreciationController@store');

        // Coa Category
        Route::post('coa/category/{id}/delete', 'CoaCategoryController@delete');
        Route::post('coa/category/store', 'CoaCategoryController@store');

        // Coa Ajax Request
        Route::get('coa/ajax/list/position/{coa_position_name}', 'CoaAjaxController@listAccountByPosition');
        Route::post('coa/ajax/create', 'CoaAjaxController@addAccount');
        Route::get('coa/fixed-asset-not-has-subledger', 'CoaController@_listFixedAssetNotHasSubledger');
        Route::get('coa/fixed-asset-has-subledger', 'CoaController@_listFixedAssetHasSubledger');
        Route::get('coa/show-category', 'CoaController@_showCategory');
        Route::get('coa/show', 'CoaController@_show');
        Route::get('coa/load-index', 'CoaController@_loadIndex');
        Route::get('coa/load-edit-form', 'CoaController@_edit');
        Route::get('coa/list-asset', 'CoaController@_listAsset');
        Route::get('coa/list-sales-income', 'CoaController@_listSalesIncome');
        Route::get('coa/list-expense', 'CoaController@_listExpense');
        Route::post('/coa/ajax-insert', 'CoaController@_insert');
        Route::post('/coa/insert-by-group', 'CoaController@_insertByGroup');
        Route::post('/coa/insert-by-category', 'CoaController@_insertByCategory');
        Route::post('/coa/update-by-category', 'CoaController@_updateByCategory');
        Route::post('/coa/state', 'CoaController@_state');

        // Coa Request Access
        Route::post('coa/import/upload', 'CoaImportController@upload');
        Route::get('coa/import/download', 'CoaImportController@download');
        Route::get('coa/import/clear', 'CoaImportController@clear');
        Route::post('coa/import', 'CoaImportController@store');
        Route::get('coa/import', 'CoaImportController@index');

        Route::post('coa/{id}/delete', 'CoaController@delete');

        //Setting Journal
        Route::get('/coa/setting-journal', 'SettingJournalController@index');
        Route::get('/coa/setting-journal/select-group', 'SettingJournalController@_selectGroup');
        Route::post('/coa/setting-journal/update-setting-journal', 'SettingJournalController@updateSettingJournal');
        
        Route::resource('coa', 'CoaController');
    });


    // Item Ajax Request
    Route::post('/item/create/ajax', 'ItemController@_create');
    Route::get('/item/price', 'ItemController@_getPrice');
    Route::get('/item/code', 'ItemController@_code');
    Route::get('/item/unit', 'ItemController@_unit');
    Route::get('/item/list', 'ItemController@_list');
    Route::post('/item/state', 'ItemController@_state');
    Route::get('/item/get-stock', 'ItemController@_getStock');
    Route::get('/item/get-quantity', 'ItemController@_getQuantity');
    Route::get('/item/list-having-quantity', 'ItemController@_listItemHavingQuantity');
    Route::get('/item/list-item-manufacture', 'ItemController@_listItemManufacture');

    // Item Barcode
    Route::post('/item/barcode/print', 'ItemBarcodeController@print');
    Route::get('/item/barcode', 'ItemBarcodeController@barcode');

    // Item Import
    Route::post('/item/import/insert', 'ItemImportController@_updateTemp');
    Route::get('/item/import/delete/{id}', 'ItemImportController@deleteRow');
    Route::post('/item/import/upload', 'ItemImportController@upload');
    Route::get('/item/import/download', 'ItemImportController@download');
    Route::get('/item/import/clear', 'ItemImportController@clearTemp');
    Route::get('/item/import/clear-error-temp', 'ItemImportController@clearErrorTemp');
    Route::post('/item/import', 'ItemImportController@store');
    Route::get('/item/import', 'ItemImportController@index');

    // Item Category
    Route::get('/item/category/list', 'ItemCategoryController@_list');
    Route::post('/item/category/delete', 'ItemCategoryController@delete');
    Route::post('/item/category/insert', 'ItemCategoryController@_insert');
    Route::post('/item/category/state', 'ItemCategoryController@_state');
    Route::resource('/item/category', 'ItemCategoryController');

    // Item Journal
    Route::get('/item/journal', 'ItemJournalController@index');
    Route::post('/item/journal/update-opening-balance', 'ItemJournalController@updateOpeningBalance');

    // Item Unit
    Route::get('/item/unit_master/list', 'UnitController@_list');
    Route::post('/item/unit_master/delete', 'UnitController@delete');
    Route::post('/item/unit_master/ajax-insert', 'UnitController@_insert');
    Route::resource('/item/unit_master', 'UnitController');

    // Item
    Route::get('/item/export', 'ItemController@export');
    Route::post('/item/delete', 'ItemController@delete');
    Route::resource('/item', 'ItemController');

    // Allocation
    Route::get('/allocation/export', 'AllocationReportController@export');
    Route::get('/allocation/report/detail/{allocation_id}', 'AllocationReportController@detail');
    Route::get('/allocation/report', 'AllocationReportController@report');
    Route::get('/allocation/list', 'AllocationController@_list');
    Route::post('/allocation/state', 'AllocationController@_state');
    Route::post('/allocation/ajax-create', 'AllocationController@_create');
    Route::post('/allocation/delete', 'AllocationController@delete');
    Route::resource('/allocation', 'AllocationController');

    // Service
    Route::get('/service/import/delete/{id}', 'ServiceImportController@deleteRow');
    Route::post('/service/import/update-temp', 'ServiceImportController@_updateTemp');
    Route::post('/service/import/upload', 'ServiceImportController@upload');
    Route::get('/service/import/download', 'ServiceImportController@download');
    Route::get('/service/import/clear', 'ServiceImportController@clearTemp');
    Route::get('/service/import/clear-error-temp', 'ServiceImportController@clearErrorTemp');
    Route::post('/service/import', 'ServiceImportController@store');
    Route::get('/service/import', 'ServiceImportController@index');

    Route::post('/service/state', 'ServiceController@_state');
    Route::get('/service/get-price', 'ServiceController@_getPrice');
    Route::get('/service/list', 'ServiceController@_list');
    Route::post('/service/delete', 'ServiceController@delete');
    Route::resource('/service', 'ServiceController');
    
    // Item Fixed Assets
    Route::get('/fixed-assets-item/get-attribute', 'FixedAssetsItemController@_getAttribute');
    Route::get('/fixed-assets-item/get-useful-life', 'FixedAssetsItemController@_getUsefulLife');
    Route::get('/fixed-assets-item/list', 'FixedAssetsItemController@_list');
    Route::post('/fixed-assets-item/state', 'FixedAssetsItemController@_state');
    Route::post('/fixed-assets-item/delete', 'FixedAssetsItemController@_delete');
    Route::resource('/fixed-assets-item', 'FixedAssetsItemController');

    // Contact
    Route::get('/contact', function () {
        return view('framework::app.master.contact.menu');
    });
    
    Route::get('/contact/{type}/import/delete/{id}', 'ContactImportController@deleteRow');
    Route::post('/contact/{type}/import/update-temp', 'ContactImportController@_updateTemp');
    Route::post('/contact/{type}/import/upload', 'ContactImportController@upload');
    Route::get('/contact/{type}/import/download', 'ContactImportController@download');
    Route::get('/contact/{type}/import/clear', 'ContactImportController@clearTemp');
    Route::get('/contact/{type}/import/clear-error-temp', 'ContactImportController@clearErrorTemp');
    Route::post('/contact/{type}/import', 'ContactImportController@store');
    Route::get('/contact/{type}/import', 'ContactImportController@index');

    Route::get('/contact/list-by-type/{slug}', 'ContactController@_listByType');
    Route::get('/contact/list', 'ContactController@_list');
    Route::get('/contact/group', 'ContactController@_listGroup');
    Route::post('/contact/state', 'ContactController@_state');
    Route::get('/contact/{person_type_id}/group/list', 'ContactGrpController@_list');
    Route::post('/contact/{person_type_id}/group/delete', 'ContactGrpController@delete');
    Route::resource('/contact/{person_type_id}/group', 'ContactGrpController');
    Route::post('/contact/{person_id}/delete', 'ContactController@delete');
    Route::get('/contact/{person_type_id}/access', 'ContactController@access');
    Route::get('/contact/{person_type_id}/access/toggle', 'ContactController@toggleAccess');
    Route::get('/contact/person/{id}', 'ContactController@url');
    Route::get('/contact/{person_type_id}', 'ContactController@index');
    Route::post('/contact/{person_type_slug}', 'ContactController@store');
    Route::get('/contact/{person_type_slug}/create', 'ContactController@create');
    Route::get('/contact/{person_type_slug}/{person_id}', 'ContactController@show');
    Route::get('/contact/{person_type_slug}/{person_id}/edit', 'ContactController@edit');
    Route::put('/contact/{person_type_slug}/{person_id}', 'ContactController@update');
    Route::post('/contact/insert/{person_type}', 'ContactController@_insert');

    Route::post('/warehouse/insert', 'WarehouseController@_insert');
    Route::get('/warehouse/list', 'WarehouseController@_list');
    Route::post('/warehouse/delete', 'WarehouseController@delete');
    Route::get('/warehouse/set-user', 'WarehouseController@setUser');
    Route::post('/warehouse/set-user', 'WarehouseController@updateUserWarehouse');
    Route::get('/warehouse', 'WarehouseController@index');
    Route::post('/warehouse', 'WarehouseController@store');
    Route::get('/warehouse/create', 'WarehouseController@create');
    Route::get('/warehouse/{warehouse_id}', 'WarehouseController@show');
    Route::get('/warehouse/{warehouse_id}/edit', 'WarehouseController@edit');
    Route::put('/warehouse/{warehouse_id}', 'WarehouseController@update');
    Route::post('/warehouse/state', 'WarehouseController@_state');
});
