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
            ->paginate(100);
        return $view;
	}

	public function print(Request $request)
	{
		dd($request->all());
		// $request = $request->input();
		// $item_id = app('request')->input('item_id');
		// $number_print = $request->input('number_of_prints');

		dd($item_id);
	}
}