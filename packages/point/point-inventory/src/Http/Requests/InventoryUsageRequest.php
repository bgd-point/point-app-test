<?php

namespace Point\PointInventory\Http\Requests;

use App\Http\Requests\Request;
use Point\PointInventory\Helpers\StockUsageHelper;

class InventoryUsageRequest extends Request
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
        $rules = [
            'form_date' => 'required',
            'warehouse_id' => 'required',
            'item_id' => 'required',
            'quantity_usage' => 'required',
            'approval_to' => 'required',
         ];

        for ($i=0 ; $i<count(\Input::get('item_id')) ; $i++) {
            $j = $i+1;

            if (! \Input::get('item_id')[$i]) {
                $rules['[row_'.$j.']_item_required'] = 'accepted';
            }
            if (number_format_db(\Input::get('quantity_usage')[$i]) == 0) {
                $rules['[row_'.$j.']_quantity_usage_cannot_be_zero'] = 'accepted';
            }
            if (number_format_db(\Input::get('stock_exist')[$i]) == 0) {
                $rules['[row_'.$j.']_quantity_item_is_empty'] = 'accepted';
            }
            if (number_format_db(\Input::get('stock_exist')) < number_format_db(\Input::get('quantity_usage'))) {
                $rules['[row_'.$j.']_quantity_too_large'] = 'accepted';
            }
            if (! \Input::get('usage_notes')[$i]) {
                $rules['[row_'.$j.']_notes_required'] = 'accepted';
            }
        }

        return $rules ;
    }

    public function messages()
    {
        return [
            'accepted' => ':attribute',
        ];
    }
}
