<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Point\Core\Models\Vesa;

class DashboardController extends Controller
{
    public function index()
    {
        $view = view('app.index');
        $view->list_vesa = Vesa::where('done', '=', false)->orderBy('task_deadline', 'asc')->orderBy('taskable_type')->get();
        return $view;
    }
}
