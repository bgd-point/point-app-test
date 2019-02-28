<?php

namespace Point\PointInventory\Http\Requests;

use App\Http\Requests\Request;
use Point\PointInventory\Helpers\StockCorrectionHelper;

class CorrectionRequest extends Request
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
            'quantity_correction' => 'required',
            'correction_notes' => 'required',
            'approval_to' => 'required',
         ];
                 
        for ($i=0 ; $i<count(\Input::get('item_id')) ; $i++) {
//            $min = 1;
//            $j = $i+1;
//
//            if (\Input::get('quantity_correction.'.$i) < 0) {
//                $min = \Input::get('stock_exist.'.$i) * -1 ;
//            }
//
//            $rules['item_id.'.$i] = 'required';
//
//            if (number_format_db(\Input::get('quantity_correction')[$i]) == 0) {
//                $rules['[row_'.$j.']_quantity_correction_cannot_be_zero'] = 'accepted';
//            }
//
//            if (! \Input::get('correction_notes')[$i]) {
//                $rules['[row_'.$j.']_correction_notes_required'] = 'accepted';
//            }
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
