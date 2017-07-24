<?php 

Route::group(['prefix' => 'manufacture/point', 'namespace' => 'Point\PointManufacture\Http\Controllers'], function () {
    Route::get('/input/approve-all', 'InputApprovalController@approveAll');
    Route::get('/input/reject-all', 'InputApprovalController@rejectAll');
    Route::get('/formula/approve-all', 'FormulaApprovalController@approveAll');
    Route::get('/formula/reject-all', 'FormulaApprovalController@rejectAll');
    Route::any('/formula/{id}/approve', 'FormulaApprovalController@approve');
    Route::any('/formula/{id}/reject', 'FormulaApprovalController@reject');
    Route::any('/input/{id}/approve', 'InputApprovalController@approve');
    Route::any('/input/{id}/reject', 'InputApprovalController@reject');
});


Route::group(['middleware' => 'auth', 'prefix' => 'manufacture/point', 'namespace' => 'Point\PointManufacture\Http\Controllers'], function () {

    // Machine
    Route::post('/machine/delete', 'MachineController@delete');
    Route::resource('/machine', 'MachineController');

    // Vesa Formula
    Route::get('formula/vesa-approval', 'FormulaVesaController@approval');

    // Formula
    Route::post('/formula/import/upload', 'FormulaImportController@upload');
    Route::get('/formula/import/download', 'FormulaImportController@download');
    Route::get('/formula/import/clear', 'FormulaImportController@clear');
    Route::post('/formula/import', 'FormulaImportController@store');
    Route::get('/formula/import', 'FormulaImportController@index');
    Route::get('/formula/request-approval', 'FormulaApprovalController@requestApproval');
    Route::post('/formula/send-request-approval', 'FormulaApprovalController@sendRequestApproval');
    Route::get('/formula/{id}/archived', 'FormulaController@archived');
    Route::resource('/formula', 'FormulaController');

    // Process
    Route::post('/process/delete', 'ProcessController@delete');
    Route::resource('/process', 'ProcessController');

    Route::get('/process-io/{process_id}', 'ProcessIOController@index');

    // Vesa Process
    Route::get('process-io/vesa-approval', 'InputVesaController@approval');
    Route::get('process-io/vesa-create-output', 'InputAfterApprovalVesaController@createOutput');

    // Process Input
    Route::post('/process-io/{process_id}/input/send-request-approval', 'InputApprovalController@sendRequestApproval');
    Route::get('/process-io/{process_id}/input/request-approval', 'InputApprovalController@requestApproval');
    Route::get('/process-io/{process_id}/input/choose-formula', 'InputController@chooseFormula');
    Route::get('/process-io/{process_id}/input/use-formula/{id}', 'InputController@useFormula');
    Route::get('/process-io/{process_id}/input/{id}/archived', 'InputController@archived');
    Route::resource('/process-io/{process_id}/input', 'InputController');

    // Process Output
    Route::get('/process-io/{process_id}/output/create-step-1', 'OutputController@createStep1');
    Route::get('/process-io/{process_id}/output/create-step-2/{id}', 'OutputController@createStep2');
    Route::get('/process-io/{process_id}/output/finished/{id}', 'OutputController@finished');
    Route::get('/process-io/{process_id}/output/{id}/archived', 'OutputController@archived');
    Route::resource('/process-io/{process_id}/output', 'OutputController');
});
