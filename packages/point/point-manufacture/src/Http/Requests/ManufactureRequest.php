<?php

namespace Point\PointManufacture\Http\Requests;

use App\Http\Requests\Request;
use Point\PointManufacture\Helpers\ManufactureHelper;

class ManufactureRequest extends Request
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
        ];

        // product items
        for ($i=0 ; $i<count(\Input::get('product_id')) ; $i++) {
            $rules['product_id.'.$i] = 'required';
        }

        for ($i=0 ; $i<count(\Input::get('product_quantity')) ; $i++) {
            $rules['product_quantity.'.$i] = "required|integer";
        }

        for ($i=0 ; $i<count(\Input::get('product_unit_id')) ; $i++) {
            $rules['product_unit_id.'.$i] = 'required';
        }

        for ($i=0 ; $i<count(\Input::get('product_warehouse_id')) ; $i++) {
            $rules['product_warehouse_id.'.$i] = 'required';
        }

        // material items
        for ($i=0 ; $i<count(\Input::get('material_id')) ; $i++) {
            $rules['material_id.'.$i] = 'required';
        }

        for ($i=0 ; $i<count(\Input::get('material_quantity')) ; $i++) {
            $date_from = date("Y-m-01 00:00:00");
            $date_to = \Input::get('form_date') ? date_format_db(\Input::get('form_date'), "end") : date("Y-m-d 23:59:59");
            $max = inventory_get_available_stock($date_from, $date_to, \Input::get('material_id.'.$i), \Input::get('warehouse_id.'.$i));
            $max = $max - 1 ;

            $rules['material_quantity.'.$i] = "required|numeric";

            if (\Input::has('machine_id')) {
                $rules['material_quantity.'.$i] = "required|numeric|min:1|max:$max";
            }
        }

        for ($i=0 ; $i<count(\Input::get('warehouse_id')) ; $i++) {
            $rules['warehouse_id.'.$i] = 'required';
        }

        for ($i=0 ; $i<count(\Input::get('material_unit')) ; $i++) {
            $rules['material_unit.'.$i] = 'required';
        }

        return $rules ;
    }
}
