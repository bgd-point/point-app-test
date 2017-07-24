<?php 

namespace Point\PointSales\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Point\Core\Helpers\ImportHelper;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\Core\Models\Timeline;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\PointSales\Models\Pos\PosPricing;
use Point\PointSales\Models\Pos\PosPricingItem;

class PosPricingImportController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        $view = view()->make('point-sales::app.sales.point.pos.pricing.import');
        $view->list_import = TempDataHelper::getPagination('pos.pricing.import', auth()->user()->id);
        $view->success = TempDataHelper::get('pos.pricing.import', auth()->user()->id, ['is_pagination' => true]);
        $view->error = TempDataHelper::get('pos.pricing.import.error', auth()->user()->id, ['is_pagination' => true]);
        $view->count_success = count(TempDataHelper::get('pos.pricing.import', auth()->user()->id, ['is_pagination' => false]));
        $view->count_error = count(TempDataHelper::get('pos.pricing.import.error', auth()->user()->id, ['is_pagination' => false]));
        
        return $view;
    }

    public function store(Request $request)
    {
        formulir_is_allowed_to_create('create.point.sales.pos.pricing', date_format_db($request->input('form_date')), []);

        $this->validate($request, [
            'form_date' => 'required',
        ]);
        $user_id = auth()->user()->id;
        $param = [
            'form_date'=> date_format_db(\Input::get('form_date')),
            'time'=> \Input::get('time'),
            'user_id'=> $user_id,
            'notes'=> \Input::get('notes'),
            'approval_status'=> \Input::get('approval_status'),
            'approval_to'=> \Input::get('approval_to')
            ];

        $request = $request->input();
        \Queue::push(function ($job) use ($request, $param) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            DB::beginTransaction();
            $formulir = FormulirHelper::create($request, 'point-sales-pos-pricing');

            $pos_pricing = new PosPricing;
            $pos_pricing->formulir_id = $formulir->id;
            $pos_pricing->save();

            $import = TempDataHelper::get('pos.pricing.import', $param['user_id']);
            for ($i=0; $i < count($import); $i++) {
                $person_group = PersonGroup::where('name', $import[$i]['group'])->where('person_type_id', 2)->first();
                $pos_pricing_item = new PosPricingItem;
                $pos_pricing_item->pos_pricing_id = $pos_pricing->id;
                $pos_pricing_item->item_id = $import[$i]['id'];
                $pos_pricing_item->person_group_id = $person_group->id;
                $pos_pricing_item->price = $import[$i]['price'];
                $pos_pricing_item->discount = $import[$i]['discount'];
                $pos_pricing_item->save();
            }

            timeline_publish('create.pos.pricing', 'add pricing pos'  . $formulir->form_number, $param['user_id']);

            TempDataHelper::clear('pos.pricing.import', $param['user_id']);
            TempDataHelper::clear('pos.pricing.import.error', $param['user_id']);

            DB::commit();

            $job->delete();
        });

        gritter_success('import pricing success, please wait a second to take a change');
        return redirect('sales/point/pos/pricing');
    }

    public function clear()
    {
        TempDataHelper::clear('pos.pricing.import', auth()->user()->id);
        TempDataHelper::clear('pos.pricing.import.error', auth()->user()->id);
        gritter_success('temporary has been cleared');
        return redirect()->back();
    }

    public function download()
    {
        \Excel::create('Pricing', function ($excel) {
            $excel->sheet('Pricing Data', function ($sheet) {
                # Initialize data
                $sheet->loadView('point-sales::app.sales.point.pos.pricing.template');
            });
        })->export('xls');
    }

    public function xlsValidate(Request $request)
    {
        $file       = $_FILES['file']['name'];
        $file_part  = pathinfo($file);
        $extension  = $file_part['extension'];
        $support_extention = array('xls', 'xlsx');
        if (!in_array($extension, $support_extention)) {
            return "false";
        }
    }
    
    public function upload(Request $request)
    {
        ImportHelper::xlsValidate();
        try {
            $filePath = $request->project->url.'/pos-pricing/';
            $fileName = auth()->user()->id.''.date('Y-m-d_His').'.xls';
            $fileLink = $filePath.$fileName;
            if (app('request')->hasFile('file')) {
                \Storage::put($fileLink, file_get_contents($request->file('file')));
            }
            TempDataHelper::clear('pos.pricing.import', auth()->user()->id);
            TempDataHelper::clear('pos.pricing.import.error', auth()->user()->id);
            $person_type = PersonType::where('slug', 'customer')->first()->toArray();
            $list_group = PersonGroup::where('person_type_id', '=', $person_type['id'])->get()->toArray();
            $user_id = auth()->user()->id;
            $request = $request->input();
            \Queue::push(function ($job) use ($fileLink, $user_id, $list_group, $request) {
                QueueHelper::reconnectAppDatabase($request['database_name']);
                \Excel::selectSheets('Pricing Data')->load('storage/app/'.$fileLink, function ($reader) use ($user_id, $list_group) {
                    $results = $reader->get()->toArray();
                    
                    foreach ($results as $data) {
                        # Check group
                        for ($i=0;$i<count($list_group);$i++) {
                            $name_group_price = strtolower(str_replace(" ", "_", $list_group[$i]['name'])."_price");
                            $name_group_discount = strtolower(str_replace(" ", "_", $list_group[$i]['name'])."_discount");
                            $group = $list_group[$i]['name'];
                            
                            # Check if item  match in database
                            $temp_name = 'pos.pricing.import.error';
                            $item = Item::where('name', $data['item'])->first();
                            if ($item) {
                                $temp_name = 'pos.pricing.import';
                            }
                            
                            # Check if price not set
                            $price = 0;
                            if (!empty($data[$name_group_price]) && is_numeric($data[$name_group_price]) && $data[$name_group_price] != '') {
                                $price = number_format_db($data[$name_group_price]) ? : 0;
                            }

                            # Check if discount is_numeric
                            $discount = 0;
                            if (!empty($data[$name_group_discount]) && is_numeric($data[$name_group_discount]) && $data[$name_group_discount] != '') {
                                $discount = number_format_db($data[$name_group_discount]) ? : 0;
                            }

                            $temp = new Temp;
                            $temp->name = $temp_name;
                            $temp->user_id = $user_id;
                            $temp->keys = serialize([
                                'id'=> $item ? $item->id : 0,
                                'code'=> $data['code'],
                                'item'=> $data['item'],
                                'price'=> $price,
                                'discount'=> $discount,
                                'group'=> $group
                            ]);
                            $temp->save();
                        }
                    }
                });
            
                $job->delete();
            });
        } catch (\Exception $e) {
            gritter_error($e->getMessage());
            return redirect()->back();
        }

        gritter_success('Upload Data Success, please wait a second and refresh this page');
        return back();
    }

    public function delete($id="")
    {
        if ($id=="") {
            gritter_error('failed to delete');
            return redirect()->back();
        }
        Temp::find($id)->delete();
        gritter_error('delete success');
        return redirect()->back();
    }

    public function _insert(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'price' => 'required',
            'item_id' => 'required',
        ]);

        $valid  = false;
        $response = array('status' =>'failed');

        if ($validator->fails()) {
            $response = array('status' =>'failed');
            return response()->json($response);
        }

        $temp = Temp::find($_POST['row_id']);
        $temp->name = 'pricing.import';
        $temp->keys = serialize([
            'id'=> $_POST['item_id'],
            'item'=> $_POST['item'],
            'price'=> number_format_db($_POST['price']),
            'discount'=> $_POST['discount'],
            'group'=> $_POST['group']
        ]);
        $temp->save();
        $response = array(
            'status' => 'success',
            'price'=> $_POST['price'],
            'discount'=> $_POST['discount'],
         );
        return response()->json($response);
    }
}
