<?php

namespace Point\Framework\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Point\Framework\Vesa\MasterVesa;

class MasterVesaController extends Controller
{
	public function settingJournal()
    {
        access_is_allowed('create.coa');

        $view = view('app.index');
        $view->array_vesa = MasterVesa::getVesaCreateSettingJournal();
        return $view;
    }

    public function stockReminder()
    {
    	access_is_allowed('create.point.purchasing.requisition');

        $view = view('app.index');
        $view->array_vesa = MasterVesa::getVesaStockreminder();
        return $view;
    }
}
