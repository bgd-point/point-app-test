<?php

Route::group(['prefix' => 'facility/ksp', 'namespace' => 'Point\Ksp\Http\Controllers'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', function () {
            return view('ksp::app.facility.ksp.menu');
        });

        // MASTER LATE CHARGES
        Route::get('/late-charges/access', 'LateChargesController@access');
        Route::resource('/late-charges', 'LateChargesController');
    });

    // LOAN SIMULATOR
    Route::group(['middleware' => 'auth'], function () {
        Route::resource('/loan-simulator', 'LoanSimulatorController', ['only' => ['index', 'store']]);
    });

    // LOAN APPLICATION
    Route::get('/loan-application/reject-all', 'LoanApplicationApprovalController@rejectAll');
    Route::get('/loan-application/approve-all', 'LoanApplicationApprovalController@approveAll');
    Route::any('/loan-application/{id}/approve', 'LoanApplicationApprovalController@approve');
    Route::any('/loan-application/{id}/reject', 'LoanApplicationApprovalController@reject');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/loan-application/request-approval', 'LoanApplicationApprovalController@requestApproval');
        Route::post('/loan-application/send-request-approval', 'LoanApplicationApprovalController@sendRequestApproval');
        Route::resource('/loan-application', 'LoanApplicationController');
    });
});
