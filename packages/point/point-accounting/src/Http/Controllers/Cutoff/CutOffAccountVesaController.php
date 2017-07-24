<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\CutOffAccount;

class CutOffAccountVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.accounting.cut.off.account');

        $view = view('app.index');
        $view->array_vesa = CutOffAccount::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.cut.off.account');

        $view = view('app.index');
        $view->array_vesa = CutOffAccount::getVesaReject();
        return $view;   
    }
}
