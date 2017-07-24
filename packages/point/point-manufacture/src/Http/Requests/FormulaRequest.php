<?php

namespace Point\PointManufacture\Http\Requests;

use App\Http\Requests\Request;

class FormulaRequest extends Request
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
            'approval_to' => 'required',
            'product_id' => 'required',
            'material_id' => 'required',
            'name' => 'required',
        ];

        // product items
        for ($i = 0; $i < count(\Input::get('product_id')); $i++) {
            if (number_format_db(\Input::get('product_quantity')[$i] < 1)) {
                $rules['row_'. $i .'_:_quantity_cannot_be_null'] = "accepted";
            }
        }

        // material items
        for ($i = 0; $i < count(\Input::get('material_id')); $i++) {
            if (number_format_db(\Input::get('material_quantity')[$i]) < 1) {
                $rules['raw_material_row_' . $i . '_cannot_null'] = 'accepted';
            }
        }

        if (\Input::get('action') == 'edit') {
            if (\Input::get('edit_notes') == '') {
                $rules['edit_notes_cannot_null'] = 'accepted';
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
}
