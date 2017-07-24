<?php

namespace Point\PointInventory\Http\Requests;

use App\Http\Requests\Request;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\PointInventory\Helpers\StockOpnameHelper;

class StockOpnameRequest extends Request
{
    public function response(array $errors)
    {
        return redirect()->back()->withErrors($errors)->withInput(\Input::all());
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        self::storeTemp(\Input::all());
        $rules = [
            'form_date' => 'required',
            'warehouse_id' => 'required',
            'quantity_opname' => 'required',
            'approval_to' => 'required',
            'item_id' => 'required',
        ];

        for ($i=0 ; $i<count(\Input::get('item_id')) ; $i++) {
            $j = $i+1;

            if (! \Input::get('item_id')[$i] || \Input::get('item_id')[$i] == '') {
                $rules['[row_'.$j.']_item_required'] = 'accepted';
            }
            if (number_format_db(\Input::get('quantity_in_warehouse')[$i]) < 0) {
                $rules['[row_'.$j.']_quantity_should_greater_than_zero'] = 'accepted';
            }
            if (! \Input::get('opname_notes')[$i]) {
                $rules['[row_'.$j.']_notes_required'] = 'accepted';
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'accepted' => ':attribute',
        ];
    }

    public function storeTemp($request)
    {
        TempDataHelper::clear('stock.opname', auth()->user()->id);
        
        for ($i=0; $i < count(app('request')->input('item_id')); $i++) {
            if (! $request['item_id'][$i]) {
                continue;
            }

            $temp = new Temp;
            $temp->user_id = auth()->user()->id;
            $temp->name = 'stock.opname';
            $temp->keys = serialize([
                'item_id'=>$request['item_id'][$i],
                'stock_in_database'=>$request['stock_in_database'][$i],
                'quantity_opname'=>$request['quantity_opname'][$i],
                'unit1'=>$request['unit1'][$i],
                'unit2'=>$request['unit2'][$i],
                'notes'=>$request['opname_notes'][$i],
                
            ]);
            $temp->save();
        }

        return true;
    }
}
