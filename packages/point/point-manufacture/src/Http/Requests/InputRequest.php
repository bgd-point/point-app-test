<?php

namespace Point\PointManufacture\Http\Requests;

use App\Http\Requests\Request;

class InputRequest extends Request
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
            'machine_id' => 'required',
            'approval_to' => 'required',

            'product_id' => 'required',
            'product_warehouse_id' => 'required',

            'material_id' => 'required',
            'material_warehouse_id' => 'required',
        ];

        // product items
        for ($i = 0; $i < count(\Input::get('product_id')); $i++) {
            if (! \Input::get('product_id')[$i]) {
                $rules['[row_'.$i.']_product_required'] = 'accepted';
            }
            if (number_format_db(\Input::get('product_quantity')[$i]) == 0) {
                $rules['[row_'.$i.']_product_quantity_required'] = 'accepted';
            }
            if (! \Input::get('product_warehouse_id')[$i]) {
                $rules['[row_'.$i.']_product_warehouse_required'] = 'accepted';
            }
        }

        // material items
        for ($i = 0; $i < count(\Input::get('material_id')); $i++) {
            $index = $i+1;
            if (! \Input::get('material_id')[$i]) {
                $rules['[row_'.$index.']_material_required'] = 'accepted';
            }
            if (number_format_db(\Input::get('material_quantity')[$i]) == 0) {
                $rules['[row_'.$index.']_material_quantity_required'] = 'accepted';
            }
            if (! \Input::get('material_warehouse_id')[$i]) {
                $rules['[row_'.$index.']_material_warehouse_required'] = 'accepted';
            }

            $date_to = \Input::get('form_date') ? date_format_db(\Input::get('form_date'),
                "end") : date("Y-m-d 23:59:59");

            if (\Input::get('action') == 'create') {
                $max = inventory_get_available_stock($date_to,
                \Input::get('material_id.' . $i),
                \Input::get('material_warehouse_id.' . $i));
                
                if (number_format_db(\Input::get('material_quantity')[$i]) > $max) {
                    $rules['raw_material_line_' . $index . '_greater_than_available_stock'] = 'accepted';
                }
            }

            if (\Input::get('action') == 'edit') {
                if (\Input::get('edit_notes') == '') {
                    $rules['edit_notes_cannot_null'] = 'accepted';
                }
            }
            

            if (number_format_db(\Input::get('material_quantity')[$i]) < 1) {
                $rules['raw_material_line_' . $index . '_cannot_null'] = 'accepted';
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
