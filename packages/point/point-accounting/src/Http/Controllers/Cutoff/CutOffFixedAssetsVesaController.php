<?php

namespace Point\PointAccounting\Http\Controllers\Cutoff;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\CutOffFixedAssets;

class CutOffFixedAssetsVesaController extends Controller
{
    public function approval()
    {
        access_is_allowed('read.point.accounting.cut.off.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = CutOffFixedAssets::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.cut.off.fixed.assets');

        $view = view('app.index');
        $view->array_vesa = CutOffFixedAssets::getVesaReject();
        return $view;
    }
}
