<?php

namespace Point\PointInventory\Http\Controllers\TransferItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Core\Helpers\UserHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Vesa\TransferItemReceiveVesa;
use Point\PointInventory\Vesa\TransferItemVesa;
use Point\PointInventory\Http\Requests\TransferItemRequest;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointInventory\Models\TransferItem\TransferItemDetail;
use Point\PointInventory\Helpers\TransferItemHelper;
use Point\PointInventory\Helpers\ReceiveItemHelper;

class ReceiveItemController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        access_is_allowed('create.point.inventory.transfer.item');
        $transfer_item = TransferItem::joinDependencies()->where('formulir.approval_status', 1)->where('formulir.form_status', 0);
        $view = view('point-inventory::app.inventory.point.transfer-item.received.index');
        $view->transfer_detail = TransferItemDetail::all();
        $view->transfer_item = $transfer_item->paginate(100);
        return $view;
    }

    public function create($id)
    {
        access_is_allowed('create.point.inventory.transfer.item');
        $view = view('point-inventory::app.inventory.point.transfer-item.received.create');
        $view->transfer_item = TransferItem::find($id);
        $view->list_item = Item::active()->paginate(2);
        $view->list_warehouse = Warehouse::active()->get();
        $view->list_user_approval = UserHelper::getAllUser();
        return $view;
    }

    public function store($id)
    {
        access_is_allowed('create.point.inventory.transfer.item');

        $receive_item = TransferItem::find($id);

        DB::beginTransaction();
        ReceiveItemHelper::create($receive_item);
        ReceiveItemHelper::updateJournal($receive_item);
        timeline_publish('create.point.inventory.transfer.item', 'create receive Transfer Item "'  . $receive_item->formulir->form_number .'"');

        DB::commit();
        gritter_success('receive item success');
        return redirect('inventory/point/transfer-item/');
    }
}
