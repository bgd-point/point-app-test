<?php

namespace Point\PointPurchasing\Http\Controllers\Inventory\Basic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\PointPurchasing\Helpers\InvoiceHelper;
use Point\PointPurchasing\Http\Requests\PurchaseRequest;
use Point\PointPurchasing\Models\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice;
use Point\PointPurchasing\Models\PurchaseOrder;

class InvoiceController extends Controller
{
    use ValidationTrait;

    public function create()
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.basic.create');
        $view->list_allocation = Allocation::all();
        return $view;
    }
    
    public function store(PurchaseRequest $request)
    {
        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.purchasing.invoice', date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request, 'point-purchasing-invoice');
        $invoice = InvoiceHelper::create($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('purchasing/point/invoice/'.$invoice->id);
    }

    public function edit($id)
    {
        $view = view('point-purchasing::app.purchasing.point.inventory.invoice.basic.edit');
        $view->invoice = Invoice::find($id);
        $view->supplier = Person::find($view->invoice->supplier_id);
        $view->list_allocation = Allocation::all();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    
    public function update(PurchaseRequest $request, $id)
    {
        $request['form_date'] = $request->input('required_date');
        DB::beginTransaction();

        $invoice = Invoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.purchasing.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::create($request, $formulir);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('purchasing/point/invoice/'.$invoice->id);
    }

    /**
     * get item unit from ajax request
     * @return \Illuminate\Http\JsonResponse
     */
    public function _unit()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $response = array('name' => '', 'converter' => '');

        $item = ItemUnit::where('item_id', \Input::get('item_id'))->where('as_default', 1)->first();
        
        if ($item) {
            $response = array('name' => $item->name, 'converter' => $item->converter );
        }

        return response()->json($response);
    }
}
