<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Models\Master\Coa;

class CoaAjaxController extends Controller
{
    use ValidationTrait;

    /**
     * Check account name is available and not used in database
     *
     * @param $name
     *
     * @return bool
     */
    private function isAvailable($key, $value)
    {
        $coa = Coa::where($key, $value)->first();

        if ($coa) {
            return false;
        }

        return true;
    }

    public function addAccount(Request $request)
    {
        access_is_allowed('create.coa');

        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'coa_category'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(array('status' => 'failed'));
        }

        if (! $this->isAvailable('name', $request->get('name'))) {
            return response()->json(array('status' => 'failed'));
        }

        $coa = new Coa;
        $coa->name = $request->get('name');
        $coa->coa_category_id = $request->get('coa_category');
        $coa->created_by = auth()->user()->id;
        $coa->updated_by = auth()->user()->id;
        $coa->save();

        return response()->json(array(
            'status' => 'success',
            'code'=> $coa->id,
            'name'=> $coa->name,
        ));
    }

    public function listAccountByPosition($coa_position_name)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $list_coa = Coa::getByPosition($coa_position_name);

        $array_coa = [];

        foreach ($list_coa as $coa) {
            array_push($array_coa, ['text' => $coa->account, 'value' => $coa->id]);
        }

        return response()->json(array(
            'lists' => $array_coa,
        ));
    }
}
