<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\User;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\FormulirLock;
use Point\PointPurchasing\Helpers\InvoiceHelper;
use Point\PointPurchasing\Helpers\ReturHelper;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\Inventory\Retur;
use Point\PointPurchasing\Models\Inventory\ReturItem;

class ReturController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.index');
        $list_retur = Retur::joinFormulir()->joinSupplier()->notArchived()->selectOriginal()->orderByStandard();
        $list_retur = ReturHelper::searchList($list_retur, \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_retur = $list_retur->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.create-step-1');
        $list_invoice = Invoice::joinFormulir()
            ->notArchived()
            ->where('formulir.approval_status', '=', 1)
            ->where('formulir.form_status', '>=', 0)
            ->orderByStandard()
            ->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function createStep2($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.create-step-2');
        $view->invoice = Invoice::find($id);
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'approval_to' => 'required',
        ]);

        DB::beginTransaction();

        $invoice = Invoice::find($request->input('invoice_id'));
        FormulirHelper::isAllowedToCreate('create.point.purchasing.return', date_format_db($request->input('form_date'), $request->input('time')), [$invoice->formulir_id]);

        $formulir = FormulirHelper::create($request, 'point-purchasing-retur');
        $retur = ReturHelper::create($request, $formulir);
        timeline_publish('create.retur', 'added new retur '  . $retur->formulir->form_number);

        DB::commit();

        gritter_success('create form success', false);
        return redirect('purchasing/point/retur/'.$retur->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.show');
        $view->retur = Retur::find($id);
        $view->invoice = FormulirHelper::getLockedModel($view->retur->formulir_id);
        $view->list_retur_archived = Retur::joinFormulir()->archived($view->retur->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_retur_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.archived');
        $view->retur_archived = Retur::find($id);
        $view->invoice = FormulirHelper::getLockedModel($view->retur_archived->formulir_id);
        $view->retur = Retur::joinFormulir()->notArchived($view->retur_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.retur.edit');
        $view->retur = Retur::find($id);
        $view->invoice = FormulirHelper::getLockedModel($view->retur->formulir_id);
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'approval_to' => 'required',
        ]);

        DB::beginTransaction();

        $retur = Retur::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.return', date_format_db($request->input('form_date'), $request->input('time')), $retur->formulir);

        $formulir_old = FormulirHelper::archive($retur->formulir_id, $request->input('edit_notes'));
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $retur = ReturHelper::create($request->input(), $formulir);
        timeline_publish('update.retur', 'update retur '  . $retur->formulir->form_number);

        DB::commit();

        gritter_success('update form success', false);
        return redirect('purchasing/point/retur/'.$retur->id);
    }
}
