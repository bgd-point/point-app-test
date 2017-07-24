<?php

namespace Point\Framework\Http\Controllers\Master\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Models\Master\History;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\SettingJournal;
use Point\Framework\Http\Controllers\Controller;

class SettingJournalController extends Controller
{
    use ValidationTrait;

    public function index()
    {
        access_is_allowed('update.coa');

        $view = view('framework::app.master.coa.setting-journal.index');
        $view->setting_journal = SettingJournal::groupBy('group')->get();
        $view->list_coa = Coa::active()->get();
        return $view;
    }

    public function _selectGroup()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $setting_journal = SettingJournal::find(\Input::get('id'));
        $groups = SettingJournal::where('group', '=', $setting_journal->group)->get();
        $list_coa = Coa::active()->get();

        $response = array(
            'groups' => ''. (String) view('framework::app.master.coa.setting-journal.__groups')->with(['groups' => $groups, 'list_coa' => $list_coa])
        );

        return response()->json($response);
    }

    public function updateSettingJournal()
    {
        $setting_journal = SettingJournal::find(\Input::get('setting_journal_id'));
        $setting_journal->coa_id = \Input::get('coa_id');
        $setting_journal->save();

        return array(
            'status' => 'success',
            'title' => 'Success',
            'msg' => 'setting journal success'
        );
    }
}
