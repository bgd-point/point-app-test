<?php

namespace Point\PointSales\Http\Controllers\Sales\Basic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Permission;
use Point\Framework\Models\Master\Person;
use Point\PointSales\Helpers\InvoiceHelper;
use Point\PointSales\Http\Requests\SalesRequest;
use Point\PointSales\Models\Sales\Invoice;

class InvoiceController extends Controller
{
    use ValidationTrait;
    
    public function create()
    {
        $view = view('point-sales::app.sales.point.sales.invoice.basic.create');
        $view->list_person = PersonHelper::getByType(['customer']);
        $view->list_item = Item::get();
        $view->list_allocation = Allocation::active()->get();
        return $view;
    }

    //store created sales invoice basic feature
    public function store(SalesRequest $request)
    {
        DB::beginTransaction();

        FormulirHelper::isAllowedToCreate('create.point.sales.invoice', date_format_db($request->input('form_date'), $request->input('time')), []);

        $formulir = FormulirHelper::create($request->input(), 'point-sales-invoice');
        $invoice = InvoiceHelper::create($request, $formulir);
        timeline_publish('create.invoice', 'added new invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('create form success', 'false');
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
    }

    //edit created sales invoice basic feature
    public function edit($id)
    {
        $view = view('point-sales::app.sales.point.sales.invoice.basic.edit');
        $view->invoice = Invoice::find($id);
        $view->person = Person::find($view->invoice->person_id);
        $view->list_item = Item::get();
        $view->list_allocation = Allocation::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    // update store created sales invoice basic feature
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'person_id' => 'required',
        ]);

        DB::beginTransaction();

        $invoice = Invoice::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.sales.invoice', date_format_db($request->input('form_date'), $request->input('time')), $invoice->formulir);

        $formulir_old = FormulirHelper::archive($request->input(), $invoice->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $invoice = InvoiceHelper::create($request, $formulir);
        timeline_publish('update.invoice', 'update invoice '  . $invoice->formulir->form_number);

        DB::commit();

        gritter_success('update form success', 'false');
        return redirect('sales/point/indirect/invoice/'.$invoice->id);
    }
}
