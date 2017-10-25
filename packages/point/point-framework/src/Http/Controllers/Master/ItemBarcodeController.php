<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Inventory;

class ItemBarcodeController extends Controller
{
    use ValidationTrait;

    public function barcode()
    {
        $view = view('framework::app.master.item.barcode');
        $view->list_inventory = Inventory::joinItem()
            ->where('inventory.total_quantity', '>', 0)
            ->groupBy('inventory.item_id')
            ->select('inventory.*')
            ->paginate(100);
        return $view;
    }

    public function print(Request $request)
    {
        $this->validate($request, [
            'inventory_id' => 'required',
            'number_of_prints' => 'required',
        ]);
        
        $view = view('framework::app.master.item.print-barcode');

        $view->inventory_rid = $request->input('inventory_rid');
        $view->inventory_id = $request->input('inventory_id');
        $view->number_print = $request->input('number_of_prints');
        $view->list_inventory = Inventory::joinItem()
            ->whereIn('inventory.id', $view->inventory_id)
            ->select('inventory.*')
            ->get();
        return $view;
    }
}
