<?php

Route::group(['prefix' => 'accounting/point', 'namespace' => 'Point\PointAccounting\Http\Controllers'], function() {
	// Approval Routes
	Route::any('/memo-journal/{id}/approve', 'MemoJournalApprovalController@approve');
	Route::any('/memo-journal/{id}/reject', 'MemoJournalApprovalController@reject');

	Route::group(['middleware' => 'auth'], function() {
		Route::get('/memo-journal/vesa-approval', 'MemoJournalVesaController@approval');
        Route::get('/memo-journal/vesa-rejected', 'MemoJournalVesaController@rejected');
		// Ajax Request
		Route::get('/memo-journal/update-master', 'MemoJournalController@_masterReference');
		Route::get('/memo-journal/update-form', 'MemoJournalController@_formReference');
		Route::post('/memo-journal/delete-temp', 'MemoJournalController@_removeTemp');
		Route::get('/memo-journal/clear-temp', 'MemoJournalController@clear');
		Route::get('/memo-journal/cancel', 'MemoJournalController@cancel');

		Route::get('/memo-journal/request-approval', 'MemoJournalApprovalController@requestApproval');
		Route::post('/memo-journal/send-request-approval', 'MemoJournalApprovalController@sendRequestApproval');

		Route::get('/memo-journal/{id}/archived', 'MemoJournalController@archived');
		Route::resource('/memo-journal', 'MemoJournalController');
	});
});
