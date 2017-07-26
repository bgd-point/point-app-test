<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\PointFinance\Models\PaymentReference;
use Point\PointPurchasing\Helpers\CashAdvanceHelper;
use Point\PointPurchasing\Models\Inventory\CashAdvance;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;

class CashAdvanceController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.cash.advance');

        $list_cash_advance = CashAdvance::joinFormulir()->joinEmployee()->notArchived()->selectOriginal();
        $list_cash_advance = CashAdvanceHelper::searchList($list_cash_advance, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        
        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.index');
        $view->list_cash_advance = $list_cash_advance->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        access_is_allowed('create.point.purchasing.cash.advance');

        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.create');
        $view->purchase_requisition = PurchaseRequisition::find($id);
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
            'employee_id' => 'required',
            'amount' => 'required',
            'approval_to' => 'required',
            'purchase_requisition_id' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.cash.advance', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-cash-advance');
        $cash_advance = CashAdvanceHelper::create($request, $formulir);
        timeline_publish('create.cash.advance', 'create cash advance ' . $cash_advance->formulir->form_number . ' success');

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/cash-advance/'.$cash_advance->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.cash.advance');

        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.show');
        $view->cash_advance = CashAdvance::find($id);
        $view->list_cash_advance_archived = CashAdvance::joinFormulir()->archived($view->cash_advance->formulir->form_number)->get();
        $view->revision = $view->list_cash_advance_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.cash.advance');

        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.archived');
        $view->cash_advance_archived = CashAdvance::find($id);
        $view->cash_advance = CashAdvance::joinFormulir()->notArchived($view->cash_advance_archived->formulir->archived)->first();
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
        access_is_allowed('update.point.purchasing.cash.advance');

        $view = view('point-purchasing::app.purchasing.point.inventory.cash-advance.edit');
        $view->cash_advance = CashAdvance::find($id);
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
            'employee_id' => 'required',
            'edit_notes' => 'required',
            'approval_to' => 'required',
            'purchase_requisition_id' => 'required',
        ]);

        if (number_format_db($request->input('amount')) < 1) {
            return redirect()->back()->withErrors('total amount must be greater than null')->withInput();
        }

        $cash_advance = CashAdvance::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.cash.advance', date_format_db($request->input('form_date')), $cash_advance->formulir);

        DB::beginTransaction();

        PaymentReference::where('payment_reference_id', $cash_advance->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $cash_advance->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $cash_advance = CashAdvanceHelper::create($request, $formulir);
        timeline_publish('update.cash.advance', 'update advance ' . $cash_advance->formulir->form_number . ' success');

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/cash-advance/'.$cash_advance->id);
    }
}
