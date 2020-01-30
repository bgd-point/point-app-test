<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Point\PointAccounting\Models\MemoJournal;
use Point\PointSales\Models\Sales\Invoice;

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'DashboardController@index');
});

Route::get('barcode', function () {
    return view('barcode');
});

Route::get('debt-age', function (\Illuminate\Http\Request $request) {
    $invoices = Invoice::join('formulir', 'formulir.id', '=', 'point_sales_invoice.formulir_id')
        ->where('formulir.form_date', '<' , $request->get('date'))
        ->where('formulir.form_status', '>=', 0)
        ->where('formulir.approval_status', '=', 1)
        ->whereNotNull('form_number')
        ->orderBy('formulir.form_date', 'asc')
        ->get();

    return view('point-sales::app.sales.point.sales.payment-collection.periode')->with('invoices', $invoices);
});

Route::get('mobile-version', function () {
    return redirect()->back()->withCookie(cookie('is-responsive', 1, 3600));
});

Route::get('desktop-version', function () {
    return redirect()->back()->withCookie(cookie('is-responsive', 0, 3600));
});

Route::get('recalculate', function () {
    \Illuminate\Support\Facades\Artisan::queue('dev:recalculate');

    return 'done';
});

Route::get('reallocation', function () {
    \Illuminate\Support\Facades\Artisan::queue('dev:reallocation');

    return 'please wait at least 3 minutes';
});
