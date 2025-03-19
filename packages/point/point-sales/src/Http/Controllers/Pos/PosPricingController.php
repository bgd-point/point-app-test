<?php

namespace Point\PointSales\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\Temp;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\PersonGroup;
use Point\PointSales\Helpers\PosImportHelper;
use Point\PointSales\Helpers\PosPricingHelper;
use Point\PointSales\Models\Pos\PosPricing;
use Point\PointSales\Models\Pos\PosPricingItem;

class PosPricingController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.sales.pos.pricing');

        $list_pricing = PosPricing::joinDependencies()->notCancelled();
        $list_pricing = PosPricingHelper::searchList($list_pricing, app('request')->input('order_by'), app('request')->input('order_type'), app('request')->input('status'), app('request')->input('date_from'), app('request')->input('date_to'), app('request')->input('search'));
        $view = view('point-sales::app.sales.point.pos.pricing.index');
        $view->list_pricing = $list_pricing->paginate(100);
        return $view;
    }

    public function indexPDF()
    {
        access_is_allowed('read.point.sales.pos.pricing');

        $list_pricing = PosPricing::joinDependencies()->notCancelled();
        $list_pricing = PosPricingHelper::searchList($list_pricing, app('request')->input('order_by'), app('request')->input('order_type'), app('request')->input('status'), app('request')->input('date_from'), app('request')->input('date_to'), app('request')->input('search'))->get();
        $pdf = \PDF::loadView('point-sales::app.sales.point.pos.pricing.index-pdf', ['list_pricing' => $list_pricing])->setPaper('a4', request()->get('database_name') == 'p_kbretail' ? 'potrait' : 'landscape');
        return $pdf->stream();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createStep1()
    {
        access_is_allowed('create.point.sales.pos.pricing');

        return view('point-sales::app.sales.point.pos.pricing.create-step-1');
    }

    public function createStep2()
    {
        access_is_allowed('create.point.sales.pos.pricing');

        $view = view('point-sales::app.sales.point.pos.pricing.create-step-2');
        $view->list_user_approval = UserHelper::getAllUser();
        $view->form_date = app('request')->input('form_date');
        $view->notes = app('request')->input('notes');
        $view->search = app('request')->input('search');
        $view->list_group = PersonGroup::where('person_type_id', 2)->get();
        $view->list_item = Item::search(0, \Input::get('search'))->orderBy('name', 'asc')->paginate(100);

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
        formulir_is_allowed_to_create('create.point.sales.pos.pricing', date_format_db($request->input('form_date')), []);

        $this->validate($request, [
            'form_date' => 'required',
        ]);

        DB::beginTransaction();

        $request->request->add(['time' => date('H:i:s')]);
        $formulir = FormulirHelper::create($request->input(), 'point-sales-pos-pricing');

        $pos_pricing = new PosPricing;
        $pos_pricing->formulir_id = $formulir->id;
        $pos_pricing->save();

        $import = \TempDataHelper::get('pos.pricing.create', auth()->user()->id, ['is_pagination' => true]);

        for ($i=0; $i < count($import); $i++) {
            $pos_pricing_item = new PosPricingItem;
            $pos_pricing_item->pos_pricing_id = $pos_pricing->id;
            $pos_pricing_item->item_id = $import[$i]['item_id'];
            $pos_pricing_item->person_group_id = $import[$i]['person_group_id'];
            $pos_pricing_item->price = $import[$i]['price'];
            $pos_pricing_item->discount = $import[$i]['discount'];
            $pos_pricing_item->save();
        }

        timeline_publish('create.pos.pricing', 'add pricing pos'  . $formulir->form_number);

        DB::commit();
        TempDataHelper::clear('pos.pricing.create', auth()->user()->id);
        gritter_success('create pricing pos success');
        return redirect('sales/point/pos/pricing');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('create.point.sales.pos.pricing');

        $view = view('point-sales::app.sales.point.pos.pricing.show');
        $view->pos_pricing = PosPricing::find($id);
        $view->list_group = PersonGroup::where('person_type_id', 2)->get();
        $view->list_pricing_detail = PosPricingItem::where('pos_pricing_id', $id)->groupBy('item_id')->paginate(100);
        return $view;
    }

    public function updatePrice()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $item_id = app('request')->input('item_id');
        $person_group_id = app('request')->input('person_group_id');
        $price = number_format_db(app('request')->input('price') ? : 0);
        $discount = number_format_db(app('request')->input('discount') ? : 0);
        $nett = $price - ($price * $discount/100);

        $row = TempDataHelper::searchKeyValue('pos.pricing.create', auth()->user()->id, ['item_id', 'person_group_id'], [$item_id, $person_group_id]);

        if ($row) {
            $data = TempDataHelper::find($row['rowid']);
            $temp = Temp::find($row['rowid']);
            $temp->name = 'pos.pricing.create';
            $temp->keys = serialize([
                'person_group_id'=> $data['person_group_id'],
                'item_id'=> $data['item_id'],
                'price'=> $price,
                'discount'=> $discount,
                'nett' => $nett
            ]);
            $temp->save();
        } else {
            $temp = new Temp;
            $temp->name = 'pos.pricing.create';
            $temp->keys = serialize([
                'person_group_id'=> $person_group_id,
                'item_id'=> $item_id,
                'price'=> $price,
                'discount'=> $discount,
                'nett' => $nett
            ]);
            $temp->save();
        }

        $response = array('nett' => $nett);

        return response()->json($response);
    }

    public function _export()
    {
        $id = app('request')->input('id');
        $storage = 'pos-pricing/';
        $fileName = auth()->user()->id.''.date('Y-m-d_His');
        \Excel::create($fileName, function ($excel) use ($id) {
            $excel->sheet('Pricing Data', function ($sheet) use ($id) {
                $pricing = PosPricing::find($id);
                $list_group = PersonGroup::where('person_type_id', 2)->get();
                $header =  array('NO', 'CODE', 'ITEM', 'QUANTITY');
                foreach ($list_group as $group) {
                    array_push($header, '['.$group->name.'] PRICE', '['.$group->name.'] DISCOUNT %', '['.$group->name.'] NETT');
                }

                // Generating header
                $content = array(
                    $header
                );
                
                // Generating content
                $list_item = Item::orderBy('name', 'asc')->get();
                $content_item = [];
                $array_merge = [];
                $i = 1;
                foreach ($list_item as $item) {
                    $array_data_item = [];
                    
                    $quantity = 0;
                    $inventory = Inventory::where('item_id', $item->id)->where('form_date', '<=', $pricing->formulir->form_date)->first();
                    $pos_pricing_item = PosPricingItem::where('pos_pricing_id', $id)->where('item_id', '=', $item->id)->first();
                    
                    if ($inventory && $pos_pricing_item) {
                        $quantity = number_format_quantity($inventory->total_quantity, 0);
                        $array_data_group = [];
                        foreach ($list_group as $group) {
                            $price = 0;
                            $discount = 0;
                            $nett = 0;
                            $pos_pricing_item = PosPricingItem::where('pos_pricing_id', $id)->where('item_id', '=', $item->id)->where('person_group_id', $group->id)->first();

                            if ($pos_pricing_item) {
                                $price = $pos_pricing_item->price;
                                $discount = $pos_pricing_item->discount;
                                $nett = $price - $price * $discount / 100;
                            }
                            array_push($array_data_group,
                                $price ? number_format_quantity($price, 0) : '0',
                                $discount ? number_format_quantity($discount, 0) : '0',
                                $nett ? number_format_quantity($nett, 0) : '0'
                            );
                        }

                        array_push($array_data_item, $i, $item->code, $item->name, $quantity);
                        $array_merge = array_merge($array_data_item, $array_data_group);
                        array_push($content, $array_merge);
                    
                        $i++;
                    }
                }

                $sheet->fromArray($content, null, 'A1', false, false);
            });
        })->store('xls', $storage);
        
        $response = array(
            'status' => 'success',
            'link' => $storage.$fileName.'.xls'
        );

        return response()->json($response);
    }
}
