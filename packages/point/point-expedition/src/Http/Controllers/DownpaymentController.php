<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\PointExpedition\Helpers\DownpaymentHelper;
use Point\PointExpedition\Models\Downpayment;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointFinance\Models\PaymentReference;

class DownpaymentController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.expedition.downpayment');

        $list_downpayment = Downpayment::joinFormulir()->joinExpedition()->notArchived()->selectOriginal();
        $list_downpayment = DownpaymentHelper::searchList($list_downpayment, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-expedition::app.expedition.point.downpayment.index');
        $view->list_downpayment = $list_downpayment->paginate(100);
        return $view;
    }

    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.expedition.downpayment');
        $list_downpayment = Downpayment::joinFormulir()->joinExpedition()->notArchived()->selectOriginal();
        $list_downpayment = DownpaymentHelper::searchList($list_downpayment, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'))->get();
        $pdf = \PDF::loadView('point-expedition::app.expedition.point.downpayment.index-pdf', ['list_downpayment' => $list_downpayment])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function createStep1()
    // {
    //     access_is_allowed('create.point.expedition.downpayment');

    //     $view = view('point-expedition::app.expedition.point.downpayment.create-step-1');
    //     $downpayment = Downpayment::select('expedition_order_id')->get()->toArray();
    //     $view->list_expedition_order = ExpeditionOrder::joinFormulir()
    //         ->joinExpedition()
    //         ->approvalApproved()
    //         ->whereNotIn('point_expedition_order.id', $downpayment)
    //         ->selectOriginal()
    //         ->paginate(100);

    //     return $view;
    // }

    public function create($id = "")
    {
        access_is_allowed('create.point.expedition.downpayment');

        $view = view('point-expedition::app.expedition.point.downpayment.create');
        $view->expedition_order = $id ? ExpeditionOrder::find($id) : '';
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_expedition = PersonHelper::getByType(['expedition']);
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'amount' => 'required',
            'approval_to' => 'required',
            'payment_type' => 'required',
        ]);

        if ($request->input('amount') == 0 || $request->input('amount') == "") {
            gritter_error('coloum amount can not empty');
            return redirect('expedition/point/downpayment/create-step-2/' . $request->input('expedition_order_id'));
        }

        FormulirHelper::isAllowedToCreate('create.point.expedition.downpayment',
            date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-expedition-downpayment');
        $downpayment = DownpaymentHelper::create($request, $formulir);
        timeline_publish('create.downpayment',
            'create downpayment ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/downpayment/' . $downpayment->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.expedition.downpayment');

        $view = view('point-expedition::app.expedition.point.downpayment.show');
        $view->downpayment = Downpayment::find($id);
        $view->expedition_order = $view->downpayment->expedition_order_id ? ExpeditionOrder::find($view->downpayment->expedition_order_id) : '';
        $view->list_downpayment_archived = Downpayment::joinFormulir()->archived($view->downpayment->formulir->form_number)->get();
        $view->revision = $view->list_downpayment_archived->count();
        
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.expedition.downpayment');

        $view = view('point-expedition::app.expedition.point.downpayment.archived');
        $view->downpayment_archived = Downpayment::find($id);
        $view->expedition_order = $view->downpayment_archived->expedition_order_id ? ExpeditionOrder::find($view->downpayment_archived->expedition_order_id) : '';
        $view->downpayment = Downpayment::joinFormulir()->notArchived($view->downpayment_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        access_is_allowed('update.point.expedition.downpayment');

        $view = view('point-expedition::app.expedition.point.downpayment.edit');
        $view->downpayment = Downpayment::find($id);
        $view->expedition_order = $view->downpayment->expedition_order_id ? ExpeditionOrder::find($view->downpayment->expedition_order_id) : '';
        $view->list_expedition = PersonHelper::getByType(['expedition']);
        ;
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'approval_to' => 'required',
            'edit_notes' => 'required',
            'payment_type' => 'required',
        ]);

        $downpayment = Downpayment::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.expedition.downpayment',
            date_format_db($request->input('form_date')), $downpayment->formulir);

        DB::beginTransaction();

        PaymentReference::where('payment_reference_id', $downpayment->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $downpayment->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $downpayment = DownpaymentHelper::create($request, $formulir);
        timeline_publish('update.downpayment', 'update deposit ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('update form success');
        return redirect('expedition/point/downpayment/' . $downpayment->id);
    }
}
