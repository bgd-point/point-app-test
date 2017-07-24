<?php

namespace Point\PointPurchasing\Http\Controllers\FixedAssets\Basic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\FixedAssetsContractReference;
use Point\Framework\Models\Master\Person;
use Point\PointPurchasing\Helpers\FixedAssets\FixedAssetsInvoiceHelper;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;

class FixedAssetsInvoiceController extends Controller
{
    use ValidationTrait;

    public function create()
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.basic.create');
        $view->list_supplier= PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');

        return $view;
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'due_date' => 'required',
            'approval_to' => 'required',
            'supplier_id' => 'required',
            'coa_id' => 'required',
            'name' => 'required',
            'item_unit' => 'required',
            'item_price' => 'required',
            'item_quantity' => 'required',
            'allocation_id' => 'required',
        ]);

        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.invoice.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request, 'point-purchasing-invoice-fixed-assets');
        $invoice = FixedAssetsInvoiceHelper::create($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/fixed-assets/invoice/'.$invoice->id);
    }

    public function edit($id)
    {
        $view = view('point-purchasing::app.purchasing.point.fixed-assets.invoice.basic.edit');
        $view->invoice = FixedAssetsInvoice::find($id);
        $view->list_supplier= PersonHelper::getByType(['supplier']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->list_allocation = Allocation::all();
        $view->list_account = Coa::getByCategory('Fixed Assets');

        return $view;
    }

    
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'due_date' => 'required',
            'approval_to' => 'required',
            'supplier_id' => 'required',
            'coa_id' => 'required',
            'name' => 'required',
            'item_unit' => 'required',
            'item_price' => 'required',
            'item_quantity' => 'required',
            'allocation_id' => 'required',
            'edit_notes' => 'required'
        ]);

        DB::beginTransaction();

        $invoice = FixedAssetsInvoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.invoice.fixed.assets', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);
        FixedAssetsContractReference::where('form_reference_id', $invoice->formulir_id)->delete();
        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = FixedAssetsInvoiceHelper::create($request, $formulir);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/fixed-assets/invoice/'.$invoice->id);
    }
}
