<?php

namespace Point\PointManufacture\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointManufacture\Models\InputProcess;

class ProcessVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('approval.point.manufacture.input');

        $view = view('app.index');
        $view->array_vesa = InputProcess::getVesaApproval();
        return $view;
    }

    public function createOutput()
    {
        access_is_allowed('create.point.manufacture.output');

        $view = view('app.index');
        $view->array_vesa = InputProcess::getVesaApproval();
        return $view;
    }
}
