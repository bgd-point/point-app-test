<?php

namespace Point\PointFinance\Http\Controllers\Bank;

use Illuminate\Auth\id;
use Illuminate\Http\Request;
use Point\Core\Exceptions\PointException;
use Point\PointAccounting\Models\AssetsRefer;
use Point\PointFinance\Models\Bank\Bank;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Http\Controllers\Controller;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;

class BankController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.finance.cashier.bank');

        $view = view('point-finance::app.finance.point.bank.index');
        $view->list_bank = Bank::joinFormulir()->joinPerson()->notArchived()->selectOriginal();
        
        if (\Input::has('order_by')) {
            $view->list_bank = $view->list_bank->orderBy(\Input::get('order_by'), \Input::get('order_type'));
        } else {
            $view->list_bank = $view->list_bank->orderByStandard();
        }

        if (\Input::get('status') != 'all') {
            $view->list_bank = $view->list_bank->where('formulir.form_status', '=', \Input::get('status') ?: 0);
        }

        if (\Input::has('date_from')) {
            $view->list_bank = $view->list_bank->where('form_date', '>=', \DateHelper::formatDB(\Input::get('date_from'), 'start'));
        }

        if (\Input::has('date_to')) {
            $view->list_bank = $view->list_bank->where('form_date', '<=', \DateHelper::formatDB(\Input::get('date_to'), 'end'));
        }

        if (\Input::has('search')) {
            $view->list_bank = $view->list_bank->where(function ($q) {
                $q->where('formulir.notes', 'like', '%'.\Input::get('search').'%')
                   ->orWhere('formulir.form_number', 'like', '%'.\Input::get('search').'%');
            });
        }

        $view->list_bank = $view->list_bank->paginate(100);

        return $view;
    }

    public function printBank(Request $request, $id)
    {
        $view = view('point-finance::app.finance.point.bank.print');
        $view->bank = Bank::find($id);
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
