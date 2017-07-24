<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\Core\Traits\ValidationTrait;
use Point\PointManufacture\Models\Process;

class ProcessIOController extends Controller
{
    use ValidationTrait;

    public function index($process_id)
    {
        $view = view('point-manufacture::app.manufacture.point.process-io');
        $view->process = Process::find($process_id);
        return $view;
    }
}
