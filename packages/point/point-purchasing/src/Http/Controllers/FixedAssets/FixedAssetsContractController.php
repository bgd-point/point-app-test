<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\FixedAssetsContract;
use Point\Framework\Models\Master\FixedAssetsContractReference;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsContractHelper;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoiceDetail;

class FixedAssetsContractController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.purchasing.contract');
        
        $list_contract = FixedAssetsContract::joinFormulir()->notArchived()->selectOriginal();
        $list_contract = FixedAssetsContractHelper::searchList(
            $list_contract,
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        );

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.index');
        $view->list_contract = $list_contract->paginate(100);
        return $view;
    }

    public function createStep1()
    {
        access_is_allowed('create.point.purchasing.contract');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.create-step-1');
        $view->list_contract_reference = FixedAssetsContractReference::whereNull('fixed_assets_contract_id')->paginate(100);
        return $view;
    }

    public function createStep2($contract_reference_id)
    {
        access_is_allowed('create.point.purchasing.contract');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.create-step-2');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');
        $view->list_account_all = Coa::active()->get();
        $view->contract_reference = FixedAssetsContractReference::find($contract_reference_id);

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
            'name' => 'required',
            'useful_life' => 'required',
            'salvage_value' => 'required',
            'purchase_date' => 'required',
            'supplier_id' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            'total_price' => 'required',
            'depreciation' => 'required',
        ]);

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.contract', date_format_db($request['form_date']), []);
        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-contract-fixed-assets');
        $contract = FixedAssetsContractHelper::create($request, $formulir);
        timeline_publish('create.point.purchasing.contract', 'added new purchasing contract '  . $contract->formulir->form_number);

        DB::commit();

        gritter_success(trans('framework::framework/global.formulir.create.success'), 'false');
        return redirect('purchasing/point/fixed-assets/contract/'.$contract->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.purchasing.contract');
        
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.show');
        $view->contract = FixedAssetsContract::find($id);
        $view->list_contract_archived = FixedAssetsContract::joinFormulir()->archived($view->contract->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_contract_archived->count();
        $view->journal = Journal::find($view->contract->journal_id);
        $view->invoice = FixedAssetsInvoice::where('formulir_id', $view->journal->form_journal_id)->first();
        $view->invoice_detail = FixedAssetsInvoiceDetail::where('fixed_assets_invoice_id', $view->invoice->id)->first();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.purchasing.contract');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.archived');
        $view->contract_archived = FixedAssetsContract::find($id);
        $view->contract = FixedAssetsContract::joinFormulir()->notArchived($view->contract_archived->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.purchasing.contract');

        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.create.edit');
        $view->contract = FixedAssetsContract::find($id);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');
        $view->list_account_all = Coa::active()->get();
        
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
            'name' => 'required',
            'useful_life' => 'required',
            'salvage_value' => 'required',
            'purchase_date' => 'required',
            'supplier_id' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            'total_price' => 'required',
            'depreciation' => 'required',
        ]);

        DB::beginTransaction();

        FixedAssetsContractHelper::updateReference($id);
        $contract = FixedAssetsContract::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.contract', date_format_db($request->input('form_date')), $contract->formulir);
        $formulir_old = FormulirHelper::archive($request->input(), $contract->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $contract = FixedAssetsContractHelper::create($request, $formulir);
        timeline_publish('update.point.purchasing.contract', 'update purchasing contract '  . $contract->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/contract/'.$contract->id);
    }

    public function createJoin($contract_reference_id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.join.create-join');
        $view->list_contract = FixedAssetsContract::joinFormulir()->notArchived()->open()->approvalApproved()->get();
        $view->contract_reference_id = $contract_reference_id;

        return $view;
    }

    public function _getDetailContract()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $contract = FixedAssetsContract::find(\Input::get('contract_id'));
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract.join._detail-contract');
        $view->contract = $contract;

        return $view;
    }

    public function storeJoin(Request $request)
    {
        $this->validate($request, [
            'contract_id' => 'required',
        ]);

        DB::beginTransaction();

        $contract = FixedAssetsContractHelper::join($request);

        DB::commit();

        gritter_success('join contract success', 'false');
        return redirect('purchasing/point/fixed-assets/contract/'.$contract->id);
    }
}
