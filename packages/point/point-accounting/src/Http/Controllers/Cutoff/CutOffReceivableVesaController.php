<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\CutOffReceivable;

class CutOffReceivableVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.accounting.cut.off.receivable');

        $view = view('app.index');
        $view->array_vesa = CutOffReceivable::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.cut.off.receivable');

        $view = view('app.index');
        $view->array_vesa = CutOffReceivable::getVesaReject();
        return $view;   
    }
}
