<?php

namespace Point\Framework\Http\Controllers\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\SettingJournal;

class ItemJournalController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->may('update.coa')) {
            return view('core::errors.restricted');
        }

        $view = view('framework::app.master.item.journal.index');
        $view->list_coa = CoaCategory::where('name', '=', 'Retained Earning')->first()->coa;
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function updateOpeningBalance(Request $request)
    {
        if (!auth()->user()->may('update.item')) {
            return view('core::errors.restricted');
        }

        $this->validate($request, [
            'retained_earning_account' => 'required'
        ]);

        DB::beginTransaction();

        $setting_journal = SettingJournal::where('group', '=', 'opening balance inventory')
            ->where('name', '=', 'retained earning')
            ->first();

        if (!$setting_journal) {
            $setting_journal = new SettingJournal;
        }

        $setting_journal->group = 'opening balance inventory';
        $setting_journal->name = 'retained earning';
        $setting_journal->coa_id = $request->input('retained_earning_account');
        $setting_journal->save();

        DB::commit();

        gritter_success('update journal success', false);

        // TODO timeline

        return redirect()->back();
    }
}
