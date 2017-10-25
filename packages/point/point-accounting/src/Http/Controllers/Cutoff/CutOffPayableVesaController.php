<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\CutOffPayable;

class CutOffPayableVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.accounting.cut.off.payable');

        $view = view('app.index');
        $view->array_vesa = CutOffPayable::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.cut.off.payable');

        $view = view('app.index');
        $view->array_vesa = CutOffPayable::getVesaReject();
        return $view;
    }
}
