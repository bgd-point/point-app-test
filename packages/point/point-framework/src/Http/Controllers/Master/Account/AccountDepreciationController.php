<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use Illuminate\Http\Request;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\AccountDepreciation;
use Point\Framework\Models\Master\Coa;

class AccountDepreciationController extends Controller
{
    use ValidationTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $response_error = array('status' => 'failed');
        $response_success = array('status' => 'success');

        $account_fixed_asset_id = \Input::get('fixed_asset_id');
        $account_depreciation_id = \Input::get('depreciation_id');

        $account_depreciation = AccountDepreciation::where('account_depreciation_id', $account_depreciation_id)->first();
        if ($account_depreciation) {
            return response()->json($response_error);
        }

        $account_depreciation_fixed_asset = AccountDepreciation::where('account_fixed_asset_id', $account_fixed_asset_id)->first();
        if (!$account_depreciation_fixed_asset) {
            $account_depreciation = new AccountDepreciation;
            $account_depreciation->account_fixed_asset_id = $account_fixed_asset_id;
            $account_depreciation->account_depreciation_id = $account_depreciation_id;
            $account_depreciation->save();

            return response()->json($response_success);
        }

        $account_depreciation_fixed_asset->account_depreciation_id = $account_depreciation_id == 0 ? null : $account_depreciation_id;
        $account_depreciation_fixed_asset->save();

        return response()->json($response_success);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $view = view('framework::app.master.coa.depreciation._show');
        $view->list_account_fixed_assets = Coa::joinCategory()->where('coa_category.name', 'Fixed Assets')
            ->where('subledger_type', 'Point\Framework\Models\FixedAsset')
            ->selectOriginal()
            ->get();

        $view->list_account_depreciations = Coa::joinCategory()->where('coa_category.name', 'Fixed Assets')
            ->where('coa.has_subledger', 0)
            ->selectOriginal()
            ->get();

        return $view;
    }
}
