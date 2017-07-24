<?php

namespace Point\PointAccounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Point\PointAccounting\Models\MemoJournal;

class MemoJournalVesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approval()
    {
        access_is_allowed('read.point.accounting.memo.journal');

        $view = view('app.index');
        $view->array_vesa = MemoJournal::getVesaApproval();
        return $view;
    }

    public function rejected()
    {
        access_is_allowed('update.point.accounting.memo.journal');

        $view = view('app.index');
        $view->array_vesa = MemoJournal::getVesaReject();
        return $view;   
    }
}
