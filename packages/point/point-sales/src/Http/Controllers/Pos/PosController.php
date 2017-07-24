<?php

namespace Point\PointSales\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\ItemUnit;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointSales\Helpers\PosHelper;
use Point\PointSales\Helpers\PosImportHelper;
use Point\PointSales\Models\Pos\Pos;
use Point\PointSales\Models\Pos\PosItem;
use Point\PointSales\Models\Pos\PosPricing;
use Point\PointSales\Models\Pos\PosPricingItem;

class PosController extends Controller
{
    use ValidationTrait;

    public function __construct()
    {
        if (! JournalHelper::checkSetup('point finance pos')) {
            gritter_error('please set your default journal');
            return redirect()->back();
        }
    }

    public function index()
    {
        access_is_allowed('read.point.sales.pos');

        $list_sales = Pos::joinDependencies();
        $list_sales = PosHelper::searchList($list_sales, \Input::get('order_by'),  \Input::get('order_type'),  \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'), \Input::get('status'));
        $view = view('point-sales::app.sales.point.pos.index');
        $view->list_sales = $list_sales->paginate(100);
        return $view;
    }

    public function create()
    {
        access_is_allowed('create.point.sales.pos');
        if (!isset(UserWarehouse::where('user_id', auth()->user()->id)->first()->warehouse_id)) {
            gritter_error('please set your warehouse before making a transaction on sales');
            return redirect("master/warehouse/set-user");
        }
        
        $view = view('point-sales::app.sales.point.pos.create-standard');
        $view->warehouse = \DB::table('user_warehouse')->join('warehouse', 'user_warehouse.warehouse_id', '=', 'warehouse.id')->where('user_warehouse.user_id', auth()->user()->id)->select('warehouse.name')->first();
        $view->list_customer = PersonHelper::getByType(['customer']);
        $person_type = PersonType::where('slug', 'customer')->first();
        $view->list_group = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($person_type);
        $results = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        if ($results) {
            $view->results = $results;
        }

        $view->carts = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        return $view;
    }

    public function store(Request $request)
    {
        formulir_is_allowed_to_create('create.point.sales.pos', date('Y-m-d', time()), []);

        $request['time'] = date('H:i:s');
        $this->validate($request, [
            'foot_money_received' => 'required',
        ]);

        DB::beginTransaction();

        $validate = self::validationQuantity($request);

        if ($validate['status'] == 'failed') {
            gritter_error('MAXIMAL STOCK OF "'.strtoupper($validate['item']->codeName).'" IS '.number_format_quantity($validate['available_stock'], 0). ' ' . $validate['item']->defaultUnit($validate['item']->id)->name);
            return redirect(url('sales/point/pos/create'));
        }

        $formulir = FormulirHelper::create($request->input(), 'point-sales-pos');
        $pos = PosHelper::create($request, $formulir);
        
        DB::commit();
        TempDataHelper::clear('pos', auth()->user()->id);

        gritter_success('sales complete');
        return redirect('sales/point/pos/'.$pos->id);
    }

    public function show($id)
    {
        access_is_allowed('read.point.sales.pos');

        $view = view('point-sales::app.sales.point.pos.show');
        $view->pos = Pos::find($id);
        $view->list_pos_archived = Pos::joinFormulir()->archived($view->pos->formulir->form_number)->orderByDate()->get();
        $view->revision = $view->list_pos_archived->count();
        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.sales.pos');

        $view = view('point-sales::app.sales.point.pos.archived');
        $view->pos_archived = Pos::find($id);
        $view->pos = Pos::joinFormulir()->notArchived($view->pos_archived->archived)->selectOriginal()->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.sales.pos');

        $view = view('point-sales::app.sales.point.pos.edit');
        $view->pos = Pos::find($id);
        session()->put('customer_id', $view->pos->customer_id);
        $view->warehouse = Warehouse::find($view->pos->warehouse_id);
        self::storeToTemp($view->pos);
        $results = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        if ($results) {
            $view->results = $results;
        }

        $view->carts = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        return $view;
    }

    public function update(Request $request, $id)
    {
        formulir_is_allowed_to_create('update.point.sales.pos', date('Y-m-d', time()), []);

        $request['time'] = date('H:i:s');
        $this->validate($request, [
            'foot_money_received' => 'required',
        ]);

        DB::beginTransaction();
        $validate = self::validationQuantity($request);

        if ($validate['status'] == 'failed') {
            gritter_error('MAXIMAL STOCK OF "'.strtoupper($validate['item']->codeName).'" IS '.number_format_quantity($validate['available_stock'], 0). ' ' . $validate['item']->defaultUnit($validate['item']->id)->name);
            return redirect(url('sales/point/pos/create'));
        }
        
        $pos = Pos::find($id);
        $formulir_old = FormulirHelper::archive($request->input(), $pos->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $pos = PosHelper::create($request, $formulir);

        DB::commit();

        TempDataHelper::clear('pos', auth()->user()->id);
        Session::forget('customer_id');

        gritter_success('sales complete');
        return redirect('sales/point/pos/'.$pos->id);
    }

    public function clear()
    {
        TempDataHelper::clear('pos', auth()->user()->id);
        Session::forget('customer_id');
        return redirect(url('sales/point/pos/create'));
    }

    public function printPos($id)
    {
        $view = view('point-sales::app.sales.point.pos.print');
        $view->pos_sales = Pos::find($id);
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);

        if ($warehouse_id > 0) {
            $view->warehouse_profiles = Warehouse::find($warehouse_id);
        }

        return $view;
    }

    public function validationQuantity($request)
    {
        $item_id = $request->input('item_id');
        $warehouse_id = PosHelper::getWarehouse();
        $quantity = $request->input('quantity');
        $response = array('status' => 'success');
        $old_quantity = $request->input('old_quantity');

        for ($i=0;$i < count($item_id); $i++) {
            if ($quantity[$i] > 0) {
                $item = Item::find($item_id[$i]);
                $available_stock = inventory_get_available_stock(\Carbon::now(), $item_id[$i], $warehouse_id);
                
                #validate stock
                if ($old_quantity) {
                    if ($old_quantity[$i]) {
                        $available_stock = $available_stock + $old_quantity[$i];
                    }
                }
                
                if (number_format_db($quantity[$i]) > $available_stock) {
                    $response = array(
                        'status' => 'failed',
                        'item' => $item,
                        'available_stock' => number_format_quantity($available_stock, 0)
                    );

                    return $response;
                    break;
                }
            }
        }

        return $response;
    }

    // Manage Cart
    public function _insert()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item_id = app('request')->input('id_item');
        $quantity = app('request')->input('qty');
        $discount = app('request')->input('discount');
        $price = app('request')->input('price');
        $customer_id = app('request')->input('customer_id');
        $warehouse = PosHelper::getWarehouse();

        $temps = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        $key = array_search($item_id, array_column($temps, 'id'), false);
        $available_stock = inventory_get_available_stock(\Carbon::now(), $item_id, $warehouse);

        if (app('request')->input('old_quantity')) {
            $available_stock = $available_stock + app('request')->input('old_quantity');
        }
        
        $status_quantity = true;
        if ($quantity > $available_stock) {
            $status_quantity = false;
        }

        $status = 'failed to update temporary table';
        

        if ($key !== false) {
            $temp = Temp::find($temps[$key]['rowid']);

            #store temp in db
            $temp->name = 'pos';
            $temp->keys = serialize([
                'id'=> $item_id,
                'warehouse_id'=> $warehouse,
                'price'=> $price,
                'discount'=> $discount,
                'qty'=> $quantity,
                'customer_id' => $customer_id
            ]);

            $temp->save();

            $status = 'success to update temporary table';
        }

        $response = array(
            'status' => $status,
            'status_quantity' => $status_quantity,
            'available_stock' => number_format_quantity($available_stock, 0)
        );

        return response()->json($response);
    }

    public function _addToCart()
    {
        $item = PosHelper::getItem(\Input::get('item_id'));
        if (! $item) {
            $response = array('status' =>'failed', 'msg' => 'item "'. $item->name .'" not found');
            return response()->json($response);
        }

        $warehouse_id = PosHelper::getWarehouse();
        if (! $warehouse_id) {
            $response = array('status' =>'failed', 'msg' => 'please set your warehouse in menu master');
            return response()->json($response);
        }

        $customer = Person::where('id', '=', \Input::get('customer_id'))->first();

        $pos_pricing_date = PosPricing::joinFormulir()
            ->select('point_sales_pos_pricing.id')
            ->where('formulir.form_date', '<=', \Carbon::now())
            ->orderBy('formulir.id', 'desc')
            ->get()
            ->toArray();

        if (! $pos_pricing_date) {
            $response = array('status' =>'failed', 'msg' => 'please create pricing first');
            return response()->json($response);
        }

        $pos_pricing = PosPricingItem::where('item_id', '=', $item->id)
            ->where('person_group_id', '=', $customer->person_group_id)
            ->whereIn('pos_pricing_id', $pos_pricing_date)
            ->orderBy('id', 'desc')
            ->get();

        if (! $pos_pricing) {
            $response = array('status' =>'failed', 'msg' => 'item "'. $item->name .'" sell price not found');
            return response()->json($response);
        }

        $price = 0;
        $discount = 0;
        foreach ($pos_pricing as $pos_price) {
            if ($pos_price->price != null) {
                $price = $pos_price->price;
                $discount = $pos_price->discount;
                break;
            } else {
                continue;
            }
        }

        if ($price == 0) {
            $response = array('status' =>'failed', 'msg' => 'item "'. $item->name .'" sell price not found');
            return response()->json($response);
        }

        $itemTmp = false;
        $temps = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);
        $key = array_search($item->id, array_column($temps, 'id'), false);
        if ($key !== false) {
            $temp = Temp::find($temps[$key]['rowid']);
            $key = unserialize($temp->keys);
            $quantity = $key['qty'] + 1;
            $discount = $key['discount'] + $discount;
            $itemTmp = true;
        } else {
            $temp = new Temp;
            $quantity = 1;
            $discount = $discount;
        }

        #validate stok inventory
        $available_stock = inventory_get_available_stock(\Carbon::now(), $item->id, $warehouse_id);

        if (number_format_db($quantity) > $available_stock) {
            $response = array('status' =>'failed', 'msg' => 'Stock of "'. $item->name .'" not available');
            return response()->json($response);
        }

        #store temp in db
        $temp->name = 'pos';
        $temp->keys = serialize([
            'id'=> $item->id,
            'warehouse_id'=> $warehouse_id,
            'price'=> $price,
            'discount'=> $discount,
            'qty'=> $quantity,
            'customer_id' => \Input::get('customer_id')
        ]);
        $temp->save();

        $total = $price * $quantity - $price * $quantity * $discount / 100;
        $unit = ItemUnit::where('item_id', $item->id)->where('converter', 1)->first();
        $response = array(
            'status' =>'success',
            'msg' => 'quantity of item '.\Input::get('item_id').' has been added',
            'id' => $item->id,
            'price' => $price,
            'discount' => $discount,
            'quantity' => $quantity,
            'nett' => $total,
            'temps' => $itemTmp,
            'item_id' => \Input::get('item_id'),
            'item_name' => $item->codeName,
            'unit' => $unit->name,
        );
        return response()->json($response);
    }

    public function removeItemCart()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $row_id = 0;
        $item_id = \Input::get('item_id');
        if (!$item_id) {
            return null;
        }

        try {
            DB::beginTransaction();
            $list_temp = TempDataHelper::get('pos', auth()->user()->id, ['is_pagination' => true]);

            foreach ($list_temp as $temp) {
                if ($temp['id'] == $item_id) {
                    $row_id = $temp['rowid'];
                }
            }

            if ($row_id) {
                TempDataHelper::remove($row_id);
            }
            DB::commit();
        } catch (\Exception $e) {
            print $row_id;
        }
        print $row_id;
    }

    public function storeToTemp($pos)
    {
        TempDataHelper::clear('pos', auth()->user()->id);
        foreach ($pos->items as $item) {
            $temp = new Temp;
            $temp->name = 'pos';
            $temp->keys = serialize([
                'id'=> $item->item_id,
                'warehouse_id'=> $item->warehouse_id,
                'price'=> $item->price,
                'discount'=> $item->discount,
                'qty'=> $item->quantity,
                'customer_id' => $pos->customer_id
            ]);
            $temp->save();
        }
    }
}
