<?php

namespace Point\PointAccounting\Http\Requests;

use App\Http\Requests\Request;
use Point\Core\Helpers\TempDataHelper;
use Point\Core\Models\Temp;
use Point\PointAccounting\Helpers\CutOffHelper;

class CutOffSubledgerRequest extends Request
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
            'foot_amount'=>'required',
            'form_date'=>'required',
            'approval_to'=>'required'
        ];
        
        if (\Input::get('foot_amount') < 1) {
            $rules['Failed_foot_credit_less_then_one'] = 'accepted';
            $rules['Failed_foot_debit_less_then_one'] = 'accepted';
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
