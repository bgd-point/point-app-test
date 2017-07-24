<?php

namespace Point\PointSales\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Point\PointSales\Helpers\InvoiceHelper;
use Point\PointSales\Helpers\ReturHelper;
use Point\PointSales\Models\Sales\Invoice;
use Point\Core\Helpers\UserHelper;
use Point\Core\User;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Permission;
use Point\PointSales\Models\Sales\Retur;

class ReturController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-sales::app.sales.point.sales.retur.index');
        $list_retur = Retur::joinFormulir()->joinPerson()->notArchived()->selectOriginal()->orderByStandard();
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
        $view = view('point-sales::app.sales.point.sales.retur.create-step-1');
        $list_invoice = Invoice::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->open()
            ->orderByStandard()
            ->selectOriginal();
        $list_invoice = InvoiceHelper::searchList($list_invoice, \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    public function createStep2($id)
    {
        $view = view('point-sales::app.sales.point.sales.retur.create-step-2');
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
        FormulirHelper::isAllowedToCreate('create.point.sales.return', date_format_db($request->input('form_date'), $request->input('time')), [$invoice->formulir_id]);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-return');
        $retur = ReturHelper::create($request, $formulir);
        timeline_publish('create.retur', 'added new retur '  . $retur->formulir->form_number);

        DB::commit();

        gritter_success('create form success', false);
        return redirect('sales/point/indirect/retur/'.$retur->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-sales::app.sales.point.sales.retur.show');
        $view->retur = Retur::find($id);
        $view->invoice = FormulirHelper::getLockedModel($view->retur->formulir_id);
        $view->list_retur_archived = Retur::joinFormulir()->archived($view->retur->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_retur_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-sales::app.sales.point.sales.retur.archived');
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
        $view = view('point-sales::app.sales.point.sales.retur.edit');
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
        FormulirHelper::isAllowedToUpdate('update.point.sales.return', date_format_db($request->input('form_date'), $request->input('time')), $retur->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $retur->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $retur = ReturHelper::create($request, $formulir);
        timeline_publish('update.retur', 'update retur '  . $retur->formulir->form_number);

        DB::commit();

        gritter_success('update form success', false);
        return redirect('sales/point/indirect/retur/'.$retur->id);
    }
}
