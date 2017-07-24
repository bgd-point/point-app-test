<?php

namespace Point\PointPurchasing\Http\Requests;

use App\Http\Requests\Request;

class FixedAssetsRequest extends Request
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
            'required_date' => 'required',
            'coa_id' => 'required',
            'approval_to' => 'required'
        ];

        // checking reason edit
        if (\Input::get('action') == 'edit') {
            $rules['edit_notes'] = 'required';
        }
        // checking supplier
        if (\Input::get('supplier_checking') == 'required') {
            if (\Input::get('supplier_id') == '') {
                $rules['supplier_id'] = 'accepted';
            }
        }

        // checking employee
        if (\Input::get('employee_checking') == 'required') {
            if (\Input::get('employee_id') == '') {
                $rules['employee_id'] = 'accepted';
            }
        }

        // name
        for ($i=0 ; $i<count(\Input::get('name')) ; $i++) {
            $rules['name.'.$i] = 'required';

            if (number_format_db(\Input::get('quantity.'.$i)) < 1) {
                $rules['quantity.'.$i] = 'accepted';
            }

            if (number_format_db(\Input::get('price.'.$i)) < 1) {
                $rules['price.'.$i] = 'accepted';
            }
        }

        // coa_id
        for ($i=0 ; $i<count(\Input::get('coa_id')) ; $i++) {
            $rules['coa_id.'.$i] = 'required';
            $rules['quantity.'.$i] = 'required';

            if (number_format_db(\Input::get('quantity.'.$i)) < 1) {
                $rules['quantity.'.$i] = 'accepted';
            }

            if (number_format_db(\Input::get('price.'.$i)) < 1) {
                $rules['price.'.$i] = 'accepted';
            }
        }

        return $rules ;
    }

    public function messages()
    {
        return [
            'accepted' => ':attribute field cannot be null value',
        ];
    }
}
