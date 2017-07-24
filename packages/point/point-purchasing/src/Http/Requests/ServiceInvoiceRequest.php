<?php

namespace Point\PointPurchasing\Http\Requests;

use App\Http\Requests\Request;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Service;

class ServiceInvoiceRequest extends Request
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
            'service_id' => 'required',
            'service_quantity' => 'required',
            'service_price' => 'required',
        ];

        // product items
        for ($i = 0; $i < count(\Input::get('service_id')); $i++) {
            $service = Service::find(\Input::get('service_id')[$i]);
            if (! $service) {
                $rules['[row_'.$i.']_service_required'] = 'accepted';
            }

            if (! \Input::get('service_id')[$i]) {
                $rules['[row_'.$i.']_service_required'] = 'accepted';
            }
            if (number_format_db(\Input::get('service_quantity')[$i]) < 1) {
                $rules['[row_'.$i.']_service_quantity_required'] = 'accepted';
            }
        }

        // item
        if (count(\Input::get('item_id')) > 0) {
            for ($i = 0; $i < count(\Input::get('item_id')); $i++) {
                $item = Item::find(\Input::get('item_id')[$i]);
                
                if (! $item) {
                    $rules['[row_'.$i.']_item_required'] = 'accepted';
                }

                if (! \Input::get('item_id')[$i]) {
                    $rules['[row_'.$i.']_item_required'] = 'accepted';
                }
                if (number_format_db(\Input::get('item_quantity')[$i]) == 0) {
                    $rules['[row_'.$i.']_item_quantity_required'] = 'accepted';
                }

                if (number_format_db(\Input::get('item_quantity')[$i]) < 1) {
                    $rules['raw_item_line_' . $i . '_cannot_null'] = 'accepted';
                }
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
