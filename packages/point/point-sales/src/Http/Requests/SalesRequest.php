<?php

namespace Point\PointSales\Http\Requests;

use App\Http\Requests\Request;

class SalesRequest extends Request
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
            'person_id' => 'required',
            'item_id' => 'required',
            'approval_to' => 'required'
        ];

        // item_id
        for ($i=0 ; $i<count(\Input::get('item_id')) ; $i++) {
            $rules['item_id.'.$i] = 'required';
            $rules['item_quantity.'.$i] = 'required';
            $rules['item_price.'.$i] = 'required';

            if (number_format_db(\Input::get('item_quantity.'.$i)) < 1) {
                $rules['item_quantity.'.$i] = 'accepted';
            }

            if (number_format_db(\Input::get('item_price.'.$i)) < 1) {
                $rules['item_price.'.$i] = 'accepted';
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
