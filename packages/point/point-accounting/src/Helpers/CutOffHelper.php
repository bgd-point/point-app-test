<?php

namespace Point\PointAccounting\Helpers;

use Carbon\Carbon;
use Point\Core\Exceptions\PointException;
use Point\Core\Helpers\DateHelper;
use Point\Framework\Helpers\AccountPayableAndReceivableHelper;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;
use Point\Framework\Models\FixedAsset;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountSubledger;
use Point\PointAccounting\Models\CutOffFixedAssets;
use Point\PointAccounting\Models\CutOffFixedAssetsDetail;
use Point\PointAccounting\Models\CutOffInventory;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointFinance\Models\PaymentReference;
use Point\PointFinance\Models\PaymentReferenceDetail;

class CutOffHelper
{
    public static function journal($cut_off)
    {
        $cut_off_account = CutOffAccount::joinFormulir()
            ->notArchived()
            ->approvalApproved()
            ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off->formulir->form_date)) . '%')
            ->orderby('formulir.id', 'desc')
            ->selectOriginal()
            ->first();

        if (! $cut_off_account) {
            // return false;            
            dd('cutoff failed - cut off account form date error');
        }

        if (! self::checkInventory($cut_off_account)) {
            // return false;
            dd('cutoff failed - cut off inventory value error');
        }

        if (! self::checkAccountPerson($cut_off_account)) {
            // return false;
            dd('cutoff failed - cut off account payable / receivable value error');
        }

        if (! self::checkFixedAssets($cut_off_account)) {
            // return false;
           dd('cutoff failed - cut off fixed asset value error');
        }

        self::insertJournal($cut_off_account);

        return true;
    }

    private static function checkFixedAssets($cut_off_account)
    {
        foreach ($cut_off_account->cutOffAccountDetail as $cut_off_account_detail) {
            if ($cut_off_account_detail->coa->subledger_type == get_class(new FixedAsset())) {
                $amount = CutOffFixedAssets::getSubledgerAmount($cut_off_account->formulir->form_date, $cut_off_account_detail->coa_id);
                $position = JournalHelper::position($cut_off_account_detail->coa_id);
                
                if (trim($cut_off_account_detail->$position) != trim($amount)) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function checkInventory($cut_off_account)
    {
        foreach ($cut_off_account->cutOffAccountDetail as $cut_off_account_detail) {
            if ($cut_off_account_detail->coa->subledger_type == get_class(new Item())) {
                $amount = CutOffInventory::getSubledgerAmount($cut_off_account->formulir->form_date, $cut_off_account_detail->coa_id);
                $position = JournalHelper::position($cut_off_account_detail->coa_id);
                $cut_off_amount = $cut_off_account_detail->$position;
                if (trim($cut_off_amount) != trim($amount)) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function checkAccountPerson($cut_off_account)
    {
        foreach ($cut_off_account->cutOffAccountDetail as $cut_off_account_detail) {
            if ($cut_off_account_detail->coa->subledger_type == get_class(new Person())) {
                $account_payable_amount = CutOffPayable::getSubledgerAmount($cut_off_account->formulir->form_date, $cut_off_account_detail->coa_id);
                $account_receivable_amount = CutOffReceivable::getSubledgerAmount($cut_off_account->formulir->form_date, $cut_off_account_detail->coa_id);
                $position = JournalHelper::position($cut_off_account_detail->coa_id);
                if (trim($cut_off_account_detail->$position) == trim($account_payable_amount)
                    || trim($cut_off_account_detail->$position) == trim($account_receivable_amount)) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private static function emptying($cut_off_account)
    {
        // INVENTORY
        $inventories = Inventory::where('form_date', '<=', $cut_off_account->formulir->form_date)
            ->select('inventory.*')
            ->groupBy('item_id')
            ->groupBy('warehouse_id')
            ->orderBy('form_date', 'desc')
            ->get();

        if ($inventories) {
            foreach($inventories as $inventory) {
                $emptying_inventory = new Inventory();
                $emptying_inventory->form_date = date('Y-m-d H:i:s');
                $emptying_inventory->formulir_id = $cut_off_account->formulir_id;
                $emptying_inventory->warehouse_id = $inventory->warehouse_id;
                $emptying_inventory->item_id = $inventory->item_id;
                $emptying_inventory->quantity = $inventory->total_quantity;
                $emptying_inventory->price = 0;

                $inventory_helper = new InventoryHelper($emptying_inventory);
                $inventory_helper->out();

                $position = JournalHelper::position($inventory->item->account_asset_id);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $inventory->item->account_asset_id;
                $journal->description = "Cut Off from formulir number " . $cut_off_account->formulir->form_number;
                $journal->$position = $inventory->total_value * -1;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $inventory->item_id;
                $journal->subledger_type = get_class(new Item());
                $journal->save();
            }
        }

        // ACCOUNT PAYABLE AND RECEIVABLE
        $list_account_payable_receivable = AccountPayableAndReceivable::where('done', 0)->get();
        foreach ($list_account_payable_receivable as $account_payable_receivable) {
            $account_payable_receivable_detail_amount = AccountPayableAndReceivableDetail::where('account_payable_and_receivable_id', $account_payable_receivable->id)->sum('amount');
            $total_debt = $account_payable_receivable->amount - $account_payable_receivable_detail_amount;
            if ($total_debt > 0 && $account_payable_receivable < $cut_off_account->formulir->form_date ) {
                $position = JournalHelper::position($account_payable_receivable->account_id);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $account_payable_receivable->account_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $total_debt * -1;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id = $account_payable_receivable->formulir_reference_id;
                $journal->subledger_id = $account_payable_receivable->person_id;
                $journal->subledger_type = get_class(new Person());
                $journal->save();

                PaymentReference::where('payment_reference_id', $cut_off_account->formulir_id)->delete();
            }
        }

        // ACCOUNT NON SUBLEDGER
        $list_co_journal = Journal::joinCoa()->whereNull('coa.subledger_type')->get();
        foreach ($list_co_journal as $co_journal) {
            $journal_value = JournalHelper::getTotalValue($co_journal->coa_id, $cut_off_account->formulir->form_date);
            $position = JournalHelper::position($co_journal->coa_id);
            $total = abs($journal_value->debit - $journal_value->credit);
            if ($total > 0) {
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $co_journal->coa_id;
                $journal->description = "Cut Off from formulir number " . $cut_off_account->formulir->form_number;
                $journal->$position = $total * -1;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id;
                $journal->subledger_type;
                $journal->save();
            }
        }

        // ACCOUNT FIXED ASSETS
        $cut_off_fixed_assets = CutOffFixedAssets::joinFormulir()
            ->approvalApproved()
            // ->open()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();
        if ($cut_off_fixed_assets) {
            foreach ($cut_off_account->cutOffAccountDetail as $cut_off_account_detail) {
                $coa = Coa::find($cut_off_account_detail->coa_id)->isFixedAssetAccount();
                if ($coa) {
                    $list_fixed_assets_detail = CutOffFixedAssetsDetail::where('fixed_assets_id', $cut_off_fixed_assets->id)->where('coa_id', $cut_off_account_detail->coa_id)->get();
                    foreach ($list_fixed_assets_detail as $fixed_assets_detail) {
                        $journal_value = JournalHelper::getTotalValue($cut_off_account_detail->coa_id, $cut_off_account->formulir->form_date);
                        $position = JournalHelper::position($cut_off_account_detail->coa_id);
                        $total = abs($journal_value->debit - $journal_value->credit);
                        if ($total > 0) {
                            $journal = new Journal();
                            $journal->form_date = date('Y-m-d H:i:s');
                            $journal->coa_id = $co_journal->coa_id;
                            $journal->description = "Cut Off from formulir number " . $cut_off_account->formulir->form_number;
                            $journal->$position = $total * -1;
                            $journal->form_journal_id = $cut_off_account->formulir_id;
                            $journal->form_reference_id;
                            $journal->subledger_id = $fixed_assets_detail->supplier_id;
                            $journal->subledger_type = get_class(new FixedAsset());
                            $journal->save();
                        }
                    }
                }
            }
        }
    }

    private static function accountInventory($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_inventory = CutOffInventory::joinFormulir()
            ->approvalApproved()
            // ->open()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        if (! $cut_off_inventory) {
            return;
        }

        // CUTOFF INVENTORY SUBLEDGER
        foreach ($cut_off_inventory->cutOffInventoryDetail as $cut_off_inventory_detail) {
            if ($cut_off_inventory_detail->stock > 0) {
                $position = JournalHelper::position($cut_off_inventory_detail->coa_id);

                // CUTOFF INVENTORY
                $inventory = new Inventory;
                $inventory->form_date = date('Y-m-d H:i:s');
                $inventory->formulir_id = $cut_off_inventory->formulir_id;
                $inventory->warehouse_id = $cut_off_inventory_detail->warehouse_id;
                $inventory->item_id = $cut_off_inventory_detail->subledger_id;
                $inventory->quantity = $cut_off_inventory_detail->stock;
                $inventory->price = $cut_off_inventory_detail->amount / $cut_off_inventory_detail->stock;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                // CUTOFF JOURNAL
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $cut_off_account_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $cut_off_inventory_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_inventory_detail->subledger_id;
                $journal->subledger_type = $cut_off_inventory_detail->subledger_type;
                $journal->save();
            }
        }

        if ($cut_off_inventory->cutOffInventoryDetail->count()) {
            FormulirHelper::close($cut_off_inventory->formulir_id);
        }
    }

    private static function accountPayable($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_payable = CutOffPayable::joinFormulir()
            ->approvalApproved()
            // ->open()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        if ($cut_off_payable) {
            foreach ($cut_off_payable->cutOffPayableDetail as $cut_off_payable_detail) {
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $cut_off_account_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->debit = 0;
                $journal->credit = $cut_off_payable_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_payable_detail->subledger_id;
                $journal->subledger_type = $cut_off_payable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_payable_detail), 'reference_id' => $cut_off_payable_detail->id]);
            }

            if ($cut_off_payable->cutOffPayableDetail->count()) {
                FormulirHelper::close($cut_off_payable->formulir_id);
            }
        }
    }

    private static function accountReceivable($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_receivable = CutOffReceivable::joinFormulir()
            ->approvalApproved()
            // ->open()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        if ($cut_off_receivable) {
            foreach ($cut_off_receivable->cutOffReceivableDetail as $cut_off_receivable_detail) {
                $journal = new Journal();
                $journal->form_date = date('Y-m-d H:i:s');
                $journal->coa_id = $cut_off_account_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->debit = $cut_off_receivable_detail->amount;
                $journal->credit = 0;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_receivable_detail->subledger_id;
                $journal->subledger_type = $cut_off_receivable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_receivable_detail), 'reference_id' => $cut_off_receivable_detail->id]);
            }

            if ($cut_off_receivable->cutOffReceivableDetail->count()) {
                FormulirHelper::close($cut_off_receivable->formulir_id);
            }
        }
    }

    private static function accountFixedAsset($cut_off_account, $cut_off_account_detail)
    {
//        $cut_off_fixed_assets = CutOffFixedAssets::joinFormulir()
//            ->approvalApproved()
        //    ->open()
//            ->notArchived()
//            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
//            ->selectOriginal()
//            ->orderby('id', 'desc')
//            ->get();
//
//        dd($cut_off_fixed_assets);
//        if ($cut_off_fixed_assets) {
//            foreach ($cut_off_fixed_assets->cutOffFixedAssetsDetail->where('coa_id', $cut_off_account_detail->coa_id) as $cut_off_fixed_assets_detail) {
//                $position = JournalHelper::position($cut_off_account_detail->coa_id);
//                $journal = new Journal();
//                $journal->form_date = date('Y-m-d H:i:s');
//                $journal->coa_id = $cut_off_account_detail->coa_id;
//                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
//                $journal->$position = $cut_off_fixed_assets_detail->total_price;
//                $journal->form_journal_id = $cut_off_account->formulir_id;
//                $journal->form_reference_id;
//                $journal->subledger_id = $cut_off_fixed_assets_detail->subledger_id;
//                $journal->subledger_type = get_class(new FixedAsset());
//                $journal->save();
//            }
//
//            if ($cut_off_fixed_assets->cutOffFixedAssetsDetail->where('coa_id', $cut_off_account_detail->coa_id)->count()) {
//                FormulirHelper::close($cut_off_fixed_assets->formulir_id);
//            }
//        }
    }

    private static function accountNonSubledger($cut_off_account, $cut_off_account_detail)
    {
        if ($cut_off_account_detail->debit > 0 || $cut_off_account_detail->credit > 0) {
            $journal = new Journal();
            $journal->form_date = date('Y-m-d H:i:s');
            $journal->coa_id = $cut_off_account_detail->coa_id;
            $journal->description = "Cut Off from formulir number " . $cut_off_account->formulir->form_number;
            $journal->debit = $cut_off_account_detail->debit;
            $journal->credit = $cut_off_account_detail->credit;
            $journal->form_journal_id = $cut_off_account->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id;
            $journal->subledger_type;
            $journal->save();
        }
    }

    private static function insertJournal($cut_off_account)
    {
        if (! $cut_off_account) {
            return false;
        }

        $cut_off_account->formulir->form_status = 1;
        $cut_off_account->formulir->save();

        self::emptying($cut_off_account);
        foreach ($cut_off_account->cutOffAccountDetail as $cut_off_account_detail) {
            if ($cut_off_account_detail->coa->has_subledger) {
                // insert inventory
                if ($cut_off_account_detail->coa->subledger_type == get_class(new Item())) {
                    self::accountInventory($cut_off_account, $cut_off_account_detail);
                }

                // insert account payable and receivable
                if ($cut_off_account_detail->coa->subledger_type == get_class(new Person())) {
                    self::accountPayable($cut_off_account, $cut_off_account_detail);
                    self::accountReceivable($cut_off_account, $cut_off_account_detail);
                }

                // insert fixed assets
                if ($cut_off_account_detail->coa->subledger_type == get_class(new FixedAsset())) {
                    // self::accountFixedAsset($cut_off_account, $cut_off_account_detail);
                    self::accountNonSubledger($cut_off_account, $cut_off_account_detail);
                }
            } else {
                // insert coa non subledger account
                self::accountNonSubledger($cut_off_account, $cut_off_account_detail);
            }
        }

        JournalHelper::checkJournalBalance($cut_off_account->formulir_id);
    }

    /**
     * Prevent generate 2 cut off on the same day
     *
     * @param $request
     * @param $formulir_type
     *
     * @return bool
     * @throws \Point\Core\Exceptions\PointException
     */
    public static function checkingDailyCutOff($request, $formulir_type)
    {
        $date_format_db = DateHelper::formatDB($request->input('form_date'));

        $formulir = Formulir::whereIn('form_status', [0, 1])
            ->where('form_date', $date_format_db)
            ->whereNotNull('form_number')
            ->where('formulirable_type', $formulir_type);

        if ($request->formulir_id) {
            $formulir->where('id', '!=', $request->formulir_id);
        }

        if ($formulir->count()) {
            throw new PointException('FAILED, NOT ALLOWED TO CREATE CUT OFF IN DUPLICATE FORM DATE');
        }

        return true;
    }

    public static function cancel($formulir_id)
    {
        $formulir = Formulir::find($formulir_id);
        
        $formulirable_type = array(
            get_class(new CutOffAccount()),
            get_class(new CutOffReceivable()),
            get_class(new CutOffPayable()),
            get_class(new CutOffInventory()),
            get_class(new CutOffFixedAssets()),
        );

        $formulir_relation = Formulir::where('form_date', $formulir->form_date)
            ->whereIn('formulirable_type', $formulirable_type)
            ->whereNotNull('form_number')
            ->get();

        if ($formulir_relation) {
            foreach ($formulir_relation as $form_relation) {
                if ($form_relation->formulirable_type != $formulir->formulirable_type) {
                    $form_relation->form_status = 0;
                    $form_relation->save();
                     
                    FormulirHelper::unlock($form_relation->id);
                    ReferHelper::cancel($form_relation->formulirable_type, $form_relation->formulirable_id);
                    InventoryHelper::remove($form_relation->id);
                    JournalHelper::remove($form_relation->id);
                    AccountPayableAndReceivableHelper::remove($form_relation->id);
                }
            }
        }

        $formulir->form_status = -1;
        $formulir->save();
    }
}