<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\FixedAssetsContractReference;
use Point\Framework\Models\Master\Gudang;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsInvoiceHelper;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;

class FixedAssetsInvoiceController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.index');
        $list_invoice = FixedAssetsInvoice::joinFormulir()->joinSupplier()->notArchived()->selectOriginal()->orderByStandard();
        $list_invoice = FixedAssetsInvoiceHelper::searchList($list_invoice, \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view->list_invoice = $list_invoice->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.create-step-1');
        $view->list_goods_received = FixedAssetsGoodsReceived::joinFormulir()
            ->availableToInvoiceGroupSupplier()
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep2($supplier_id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.create-step-2');
        $view->supplier_id = $supplier_id;
        $view->list_goods_received = FixedAssetsGoodsReceived::joinFormulir()
            ->availableToInvoice($supplier_id)
            ->selectOriginal()
            ->paginate(100);
        return $view;
    }

    public function createStep3()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.create-step-3');
        $array_goods_received_id = explode(',', \Input::get('goods_received_id'));
        $view->supplier = Person::find(\Input::get('supplier_id'));
        $view->list_goods_received = FixedAssetsGoodsReceived::joinFormulir()
            ->whereIn('point_purchasing_fixed_assets_goods_received.formulir_id', $array_goods_received_id)
            ->selectOriginal()
            ->get();
        $view->purchase_order_tax = $view->list_goods_received->first()->purchaseOrder->type_of_tax;
        $view->purchase_order_discount = $view->list_goods_received->first()->purchaseOrder->discount;
        $view->purchase_order_expedition_fee = $view->list_goods_received->first()->purchaseOrder->expedition_fee;

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
        $validator = \Validator::make($request->all(), [
            'form_date' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back();
        }

        DB::beginTransaction();

        $formulir_id = [];
        $references = [];
        $references_id = $request->input('reference_id');
        $references_type = $request->input('reference_type');
        for ($i=0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        FormulirHelper::isAllowedToCreate('create.point.purchasing.invoice.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), $formulir_id);

        $formulir = FormulirHelper::create($request->input(), 'point-purchasing-invoice-fixed-assets');
        $invoice = FixedAssetsInvoiceHelper::create($request, $formulir, $references);
        timeline_publish('create.point.purchasing.invoice.fixed.assets', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/invoice/'.$invoice->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.show');
        $view->invoice = FixedAssetsInvoice::find($id);
        $view->list_invoice_archived = FixedAssetsInvoice::joinFormulir()->archived($view->invoice->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_invoice_archived->count();
        return $view;
    }

    public function archived($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.archived');
        $view->invoice_archived = FixedAssetsInvoice::find($id);
        $view->invoice = FixedAssetsInvoice::joinFormulir()->notArchived($view->invoice_archived->formulir->archived)->selectOriginal()->first();
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
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.edit');
        $view->invoice = FixedAssetsInvoice::find($id);
        $view->supplier = Person::find($view->invoice->supplier_id);
        $array_goods_received_id = FormulirHelper::getLockedModelIds($view->invoice->formulir_id);
        $view->list_goods_received = FixedAssetsGoodsReceived::joinFormulir()
            ->whereIn('point_purchasing_fixed_assets_goods_received.id', $array_goods_received_id)
            ->selectOriginal()
            ->get();
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
        $validator = \Validator::make($request->all(), [
            'form_date' => 'required',
            'due_date' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('purchasing/point/fixed-assets/invoice/create-step-1');
        }

        DB::beginTransaction();

        $references_type = $request->input('reference_type');
        $references_id = $request->input('reference_id');
        $formulir_id = [];
        $references = [];

        for ($i=0; $i < count($references_type); $i++) {
            $reference = $references_type[$i]::find($references_id[$i]);
            array_push($references, $reference);
            array_push($formulir_id, $reference->formulir_id);
        }

        $invoice = FixedAssetsInvoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.invoice.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);
        FixedAssetsContractReference::where('form_reference_id', $invoice->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = FixedAssetsInvoiceHelper::create($request, $formulir, $references);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/fixed-assets/invoice/'.$invoice->id);
    }

    public function _list()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $list_invoice = FixedAssetsInvoice::joinFormulir()->notArchived()->get();
        
        $list_form_invoice = [];
        foreach ($list_invoice as $invoice) {
            $text = 'INVOICE NUMBER ' . $invoice->formulir->form_number . ' DATE '. date_format_view($invoice->formulir->form_date);
            if ($invoice->formulir->notes) {
                $text = $text .' ' .$invoice->formulir->notes;
            }

            array_push($list_form_invoice, ['text'=> $text, 'value'=>$invoice->formulir->formulirable_id]);
        }
        $response = array(
            'lists' => $list_form_invoice
        );
        return response()->json($response);
    }

    public function _search()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $id = \Input::get('id');
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.contract._detail-invoice');
        $view->invoice = FixedAssetsInvoice::find($id);

        return $view;
    }
}
