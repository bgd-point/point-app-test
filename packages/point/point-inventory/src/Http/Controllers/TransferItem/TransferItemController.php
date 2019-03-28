<?php

namespace Point\PointInventory\Http\Controllers\TransferItem;

use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Core\Helpers\UserHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Vesa\TransferItemReceiveVesa;
use Point\PointInventory\Http\Requests\TransferItemRequest;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointInventory\Helpers\TransferItemHelper;

class TransferItemController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.inventory.transfer.item');

        $transfer_item = TransferItem::joinDependencies();
        $transfer_item = TransferItemHelper::searchList($transfer_item, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
        $view = view('point-inventory::app.inventory.point.transfer-item.index');
        $view->transfer_item = $transfer_item->paginate(100);
        return $view;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illumoutate\Http\Response
     */
    public function create()
    {
        access_is_allowed('create.point.inventory.transfer.item');

        $view = view('point-inventory::app.inventory.point.transfer-item.send.create');
        $view->list_item = Item::all();
        $view->list_warehouse = Warehouse::all();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function store(TransferItemRequest $request)
    {
        formulir_is_allowed_to_create('create.point.inventory.transfer.item', date_format_db($request->input('form_date')), []);

        DB::beginTransaction();

        $formulir = formulir_create($request->input(), 'point-inventory-transfer-item');
        $transfer_item = TransferItemHelper::create($formulir);
        timeline_publish('create.point.inventory.transfer.item', 'user '.auth()->user()->name.' successfully create transfer item ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form transfer item "'. $formulir->form_number .'" Success to add');
        return redirect('inventory/point/transfer-item/send/'.$transfer_item->id);
    }

    public function show($id)
    {
        access_is_allowed('read.point.inventory.transfer.item');

        $view = view('point-inventory::app.inventory.point.transfer-item.send.show');
        $view->transfer_item = TransferItem::find($id);
        $view->transfer_item_archived = TransferItem::joinFormulir()->archived($view->transfer_item->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->transfer_item_archived->count();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.inventory.transfer.item');

        $view = view('point-inventory::app.inventory.point.transfer-item.send.edit');
        $view->transfer_item = TransferItem::find($id);
        $view->list_item = Item::active()->paginate(2);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.inventory.transfer.item');

        $view = view('point-inventory::app.inventory.point.transfer-item.send.archived');
        $view->transfer_item_archived = TransferItem::find($id);
        $view->transfer_item = TransferItem::joinFormulir()->notArchived($view->transfer_item_archived->formulir->archived)->selectOriginal()->first();
        return $view;
    }

    public function update(TransferItemRequest $request, $id)
    {
        $formulir_check = Formulir::find($id);

        formulir_is_allowed_to_update('update.point.inventory.transfer.item', $formulir_check->form_date, $formulir_check);

        $this->validate($request, [
            'edit_notes' => 'required',
        ]);

        DB::beginTransaction();

        $formulir_old = formulir_archive($request->input(), $formulir_check->id);
        $formulir = FormulirHelper::update($request->input(), $formulir_check->form_number, $formulir_check->form_raw_number);
        $transfer_item = TransferItemHelper::create($formulir);
        timeline_publish('update.point.inventory.transfer.item', 'user '.\Auth::user()->name.' successfully update transfer item ' . $formulir->form_number);

        DB::commit();

        gritter_success('Form transfer item "'. $formulir->form_number .'" Success to update', 'false');
        return redirect('inventory/point/transfer-item/send/'.$transfer_item->id);
    }

    public function printPdf($id)
    {
        $transferItem = TransferItem::find($id);

        $data = array(
            'transferItem' => $transferItem,
            'warehouse' => $transferItem->warehouseFrom
        );

        $pdf = \PDF::loadView('point-inventory::app.inventory.point.transfer-item.send.print', $data);
        return $pdf->stream($transferItem->formulir->form_number.'.pdf');
    }
}
