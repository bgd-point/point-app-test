<?php

namespace Point\Core\Http\Controllers\Setting;

use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\Core\Http\Controllers\Controller;

class ResetDatabaseController extends Controller
{
    public function index()
    {
        if (app('request')->input('database_name') != 'p_test') {
            throw new PointException('Access denied');
        }

        return view('core::app.settings.reset-database');
    }

    public function toDefault(Request $request)
    {
        if (app('request')->input('database_name') != 'p_test') {
            throw new PointException('Access denied');
        }

        $request = $request->input();

        // setup database client
        \Artisan::call('reset-database', [
            'database_name' => $request['database_name']
        ]);

        gritter_success('reset database in progress, we will notify you via email when it done');

        return redirect()->back();
    }
}
