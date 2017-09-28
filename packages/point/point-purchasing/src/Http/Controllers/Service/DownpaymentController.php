<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Helpers\ServiceDownpaymentHelper;
use Point\PointPurchasing\Models\Service\Downpayment;

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
        access_is_allowed('read.point.purchasing.service.downpayment');

        $list_downpayment = Downpayment::joinFormulir()->joinSupplier()->notArchived()->selectOriginal();
        $list_downpayment = ServiceDownpaymentHelper::searchList($list_downpayment, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.index');

        $view->list_downpayment = $list_downpayment->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id="")
    {
        access_is_allowed('create.point.purchasing.service.downpayment');

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.create');

        $view->purchase_order = "";
        $view->list_supplier = PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'supplier')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
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
            'supplier_id' => 'required',
            'amount' => 'required',
            'approval_to' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.service.downpayment', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-service-downpayment');
        $downpayment = ServiceDownpaymentHelper::create($request, $formulir);
        timeline_publish('create.downpayment', 'create downpayment ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/service/downpayment/'.$downpayment->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.service.downpayment');

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.show');
        $view->downpayment = Downpayment::find($id);
        $view->list_downpayment_archived = Downpayment::joinFormulir()->archived($view->downpayment->formulir->form_number)->get();
        $view->revision = $view->list_downpayment_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.service.downpayment');

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.archived');
        $view->downpayment_archived = Downpayment::find($id);
        $view->downpayment = Downpayment::joinFormulir()->notArchived($view->downpayment_archived->formulir->archived)->first();
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
        access_is_allowed('update.point.purchasing.service.downpayment');

        $view = view('point-purchasing::app.purchasing.point.service.downpayment.edit');
        $view->downpayment = Downpayment::find($id);
        $view->payment_reference = PaymentReference::where('payment_reference_id', '=', $view->downpayment->formulir_id)->first();
        $view->list_supplier = PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $person_type = PersonType::where('slug', 'supplier')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
        
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
            'supplier_id' => 'required',
            'edit_notes' => 'required',
            'approval_to' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        $downpayment = Downpayment::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.service.downpayment', date_format_db($request->input('form_date')), $downpayment->formulir);

        DB::beginTransaction();

        PaymentReference::where('payment_reference_id', $downpayment->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $downpayment->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $downpayment = ServiceDownpaymentHelper::create($request, $formulir);
        timeline_publish('update.downpayment', 'update deposit ' . $downpayment->formulir->form_number . ' success');

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/service/downpayment/'.$downpayment->id);
    }
}
