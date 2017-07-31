<?php

namespace Point\PointFinance\Http\Controllers\Cheque;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Models\Cheque\Cheque;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;

class ChequeController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.finance.cashier.cheque');

        $view = view('point-finance::app.finance.point.cheque.index');
        $view->list_cheque = Cheque::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        
        if (\Input::has('order_by')) {
            $view->list_cheque = $view->list_cheque->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_cheque = $view->list_cheque->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_cheque = $view->list_cheque->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::has('date_from')) {
            $view->list_cheque = $view->list_cheque->where('form_date', '>=', \DateHelper::formatDB(\Input::get('date_from'), 'start'));
        }

        if (\Input::has('date_to')) {
            $view->list_cheque = $view->list_cheque->where('form_date', '<=', \DateHelper::formatDB(\Input::get('date_to'), 'end'));
        }

        if (\Input::has('search')) {
            $view->list_cheque = $view->list_cheque->where(function ($q) {
                $q->where('formulir.notes', 'like', '%'.\Input::get('search').'%')
                   ->orWhere('formulir.form_number', 'like', '%'.\Input::get('search').'%');
            });
        }

        $view->list_cheque = $view->list_cheque->paginate(100);

        return $view;
    }

    public function printCheque(Request $request, $id)
    {
        $view = view('point-finance::app.finance.point.cheque.print');
        $view->cheque = Cheque::find($id);
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $view->warehouse_profiles = Warehouse::find($warehouse_id);
        } else {
            $view->warehouse_profiles = Warehouse::first();
        }
        if (!$view->warehouse_profiles) {
            throw new PointException('Please create your warehouse first to set your default name, address and phone number');
        }
        $view->project_name = $request->get('project')->name;
        return $view;
    }
}
