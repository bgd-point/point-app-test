<?php

namespace Point\Framework\Http\Controllers\Accounting;

use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\AccountingHelper;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\CoaSaldo;
use Point\Framework\Models\FixedAsset;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;

class SubLedgerController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date_from = date('Y-m-01 00:00:00');
        $date_to = date('Y-m-d 23:59:59');
        $view = view('framework::app.accounting.sub-ledger.index');
        $view->list_coa = Coa::active()->whereNotNull('subledger_type')->get();
        $view->coa_id = \Input::get('coa_filter') ? : 0;
        $view->date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : $date_from;
        $view->date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : $date_to;
        $view->subledger_id = \Input::get('subledger_id') ?: 0;
        $view->journals = AccountingHelper::querySubledger($view->date_from, $view->date_to, $view->subledger_id, $view->coa_id);

        return $view;
    }

    public function export()
    {
        $file_name = 'Subledger '.auth()->user()->id . '' . date('Y-m-d_His');
        $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : date('Y-m-01 00:00:00');
        $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : date('Y-m-d 23:59:59');
        $coa_id = \Input::get('coa_filter') ? : 0;
        $subledger_id = \Input::get('subledger_id') ?: 0;
        $journals = AccountingHelper::querySubledger($date_from, $date_to, $subledger_id, $coa_id);
        
        \Excel::create($file_name, function($excel) use ($date_from, $date_to, $coa_id, $subledger_id, $journals) {

            $excel->sheet('Subledger', function($sheet) use ($date_from, $date_to, $coa_id, $subledger_id, $journals) {
                $data = array(
                    'list_coa' => Coa::active()->whereNotNull('subledger_type')->get(),
                    'coa_id' => $coa_id,
                    'subledger_id' => $subledger_id,
                    'journals' => $journals,
                    'date_to' => $date_to,
                    'date_from' => $date_from
                 );
                
                $sheet->loadView('framework::app.accounting.sub-ledger._data', $data);
            });

        })->export('xls');
    }

    public function _coa()
    {
        if (!$this->validateCSRF()) {
            return response()->json($this->restrictionAccessMessage());
        }

        $list_subleder = self::getSubledger(\Input::get('coa_id'));
        if (! $list_subleder) {
            return false;
        }

        echo '<select class="selectize" name="subledger_id" id="subledger-id" style="width: 100%;" data-placeholder="Choose one..">';
        echo '<option></option>';
        echo '<option value="all">All</option>';
        foreach ($list_subleder as $subledger) {
            echo '<option value="'.$subledger->id.'">'.$subledger->codeName.'</option>';
        }
        echo '</select>';
    }

    public static function getSubledger($coa_id)
    {
        $coa = Coa::find($coa_id);
        if ($coa->subledger_type == get_class(new Person())) {
            return Person::active()->get();
        }

        if ($coa->subledger_type == get_class(new Item())) {
            return Item::active()->get();
        }

        if ($coa->subledger_type == get_class(new FixedAsset())) {
            // Account fixed Asset not availble in this moment
            return null;
        }
    }
}
