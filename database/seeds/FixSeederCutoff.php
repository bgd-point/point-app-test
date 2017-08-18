<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\AccountPayableAndReceivableDetail;
use Point\Framework\Models\FixedAsset;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffFixedAssets;
use Point\PointAccounting\Models\CutOffFixedAssetsDetail;
use Point\PointAccounting\Models\CutOffInventory;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointFinance\Models\PaymentReference;

class FixSeederCutoff extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        $list_cutoff_account = CutOffAccount::joinFormulir()->close()->notArchived()->approvalApproved()->selectOriginal()->get();
        foreach ($list_cutoff_account as $cutoff_account) {
            // $cutoff_account->formulir->form_date = '2017-03-31 16:59:59';
            // $cutoff_account->formulir->save();
            $journal = Journal::where('form_journal_id', $cutoff_account->formulir_id )->selectRaw('sum(debit) as debit, sum(credit) as credit')->first();
            \Log::info('before :: debit = ' . $journal->debit. ' credit = '.$journal->credit);
            $journal = Journal::where('form_journal_id', $cutoff_account->formulir_id)->delete();
            $account_payable_receivable = AccountPayableAndReceivable::where('formulir_reference_id', $cutoff_account->formulir_id)->delete();
            $inventory = Inventory::where('formulir_id', $cutoff_account->formulir_id)->delete();
        	foreach ($cutoff_account->cutOffAccountDetail as $cut_off_account_detail) {
        		if ($cut_off_account_detail->coa->has_subledger) {
                    // insert inventory
	                if ($cut_off_account_detail->coa->subledger_type == get_class(new Item())) {
	                    self::accountInventory($cutoff_account, $cut_off_account_detail);
	                }

	                // insert account payable and receivable
	                if ($cut_off_account_detail->coa->subledger_type == get_class(new Person())) {
	                    self::accountPayable($cutoff_account, $cut_off_account_detail);
	                    self::accountReceivable($cutoff_account, $cut_off_account_detail);
	                }

                    // insert fixed assets
                    if ($cut_off_account_detail->coa->subledger_type == get_class(new FixedAsset())) {
                        self::accountFixedAsset($cutoff_account, $cut_off_account_detail);
                    }
	            } else {
                    self::accountNonSubledger($cutoff_account, $cut_off_account_detail);
                }
        	}

            JournalHelper::checkJournalBalance($cutoff_account->formulir_id);

        }
        \DB::commit();
    }

    private static function fixInventory($formulir_id)
    {
        $inventories = Inventory::where('formulir_id', $formulir_id)->get();
        foreach ($inventories as $inventory) {
            $inventory->form_date = '2017-03-30 16:59:59';
            $inventory->save();
        }
    }

    private static function emptying($cut_off_account)
    {
        // INVENTORY
        $inventories = Inventory::where('form_date', '<=', $cut_off_account->formulir->form_date)
            ->select('inventory.*')
            ->orderBy('form_date', 'desc')
            ->get();
        \Log::info($cut_off_account->formulir->form_date);
        foreach ($inventories as $inventory) {
            $emptying_inventory = new Inventory();
            $emptying_inventory->form_date = $cut_off_account->formulir->form_date;
            $emptying_inventory->formulir_id = $cut_off_account->formulir_id;
            $emptying_inventory->warehouse_id = $inventory->warehouse_id;
            $emptying_inventory->item_id = $inventory->item_id;
            $emptying_inventory->quantity = $inventory->total_quantity;
            $emptying_inventory->price = 0;

            $inventory_helper = new InventoryHelper($emptying_inventory);
            $inventory_helper->out();

            $position = JournalHelper::position($inventory->item->account_asset_id);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
            $journal->coa_id = $inventory->item->account_asset_id;
            $journal->description = "Cut Off from formulir number " . $cut_off_account->formulir->form_number;
            $journal->$position = $inventory->total_value * -1;
            $journal->form_journal_id = $cut_off_account->formulir_id;
            $journal->form_reference_id;
            $journal->subledger_id = $inventory->item_id;
            $journal->subledger_type = get_class(new Item());
            $journal->save();
        }

        // ACCOUNT PAYABLE AND RECEIVABLE
        $list_account_payable_receivable = AccountPayableAndReceivable::where('done', 0)->get();
        foreach ($list_account_payable_receivable as $account_payable_receivable) {
            $account_payable_receivable_detail_amount = AccountPayableAndReceivableDetail::where('account_payable_and_receivable_id', $account_payable_receivable->id)->sum('amount');
            $total_debt = $account_payable_receivable->amount - $account_payable_receivable_detail_amount;
            if ($total_debt > 0) {
                $position = JournalHelper::position($account_payable_receivable->account_id);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
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
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
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
            ->open()
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
                            $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
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

    private static function accountNonSubledger($cut_off_account, $cut_off_account_detail)
    {
        if ($cut_off_account_detail->debit > 0 || $cut_off_account_detail->credit > 0) {
            \Log::info('journal account non subledger started - '.$cut_off_account_detail->coa->account .' debit : ' . $cut_off_account_detail->debit .' credit : '.$cut_off_account_detail->credit);
            $journal = new Journal();
            $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
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

    private static function accountPayable($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_payable = CutOffPayable::joinFormulir()
            ->approvalApproved()
            ->close()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        $total = 0;
        if ($cut_off_payable) {
            foreach($cut_off_payable->cutOffPayableDetail->where('coa_id', $cut_off_account_detail->coa_id) as $cut_off_payable_detail) {
                $position = $cut_off_account_detail->debit ? 'debit' : 'credit';
            	
                \Log::info('journal account payable started - ' . $cut_off_account_detail->coa->account . ' '. $position. ' '. $cut_off_payable_detail->amount);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_payable_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $cut_off_payable_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_payable_detail->subledger_id;
                $journal->subledger_type = $cut_off_payable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_payable_detail), 'reference_id' => $cut_off_payable_detail->id]);

                $total += $cut_off_payable_detail->amount;
            }
        }

        $total ? \Log::info('-------- total :'. $total) : '';
    }

    private static function accountReceivable($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_receivable = CutOffReceivable::joinFormulir()
            ->approvalApproved()
            ->close()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        $total = 0;
        if ($cut_off_receivable) {
            foreach($cut_off_receivable->cutOffReceivableDetail->where('coa_id', $cut_off_account_detail->coa_id) as $cut_off_receivable_detail) {
            	$position = $cut_off_account_detail->debit ? 'debit' : 'credit';
                \Log::info('journal account receivable started  - ' . $cut_off_account_detail->coa->account . ' ' . $position. ' '. $cut_off_receivable_detail->amount);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_receivable_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $cut_off_receivable_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_receivable_detail->subledger_id;
                $journal->subledger_type = $cut_off_receivable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_receivable_detail), 'reference_id' => $cut_off_receivable_detail->id]);

                $total += $cut_off_receivable_detail->amount;
            }
        }
        $total ? \Log::info('-------- total :'. $total) : '';
    }

    private static function accountInventory($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_inventory = CutOffInventory::joinFormulir()
            ->approvalApproved()
            ->close()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        if(! $cut_off_inventory) {
            return;
        }

        $total = 0;
        // CUTOFF INVENTORY SUBLEDGER
        foreach($cut_off_inventory->cutOffInventoryDetail->where('coa_id', $cut_off_account_detail->coa_id) as $cut_off_inventory_detail) {
            // EMPTY JOURNAL
            $coa_value = JournalHelper::getTotalValueBySubledger($cut_off_inventory_detail->coa_id, $cut_off_account->formulir->form_date, $cut_off_inventory_detail->subledger_type, $cut_off_inventory_detail->subledger_id);
            if ($cut_off_inventory_detail->stock > 0 && $cut_off_inventory_detail->amount > 0) {
                $position = $cut_off_account_detail->debit ? 'debit' : 'credit';
                \Log::info('journal inventory started - ' . $cut_off_account_detail->coa->account . ' ' . $position . ' ' . $cut_off_inventory_detail->amount);

                // CUTOFF INVENTORY
                $inventory = new Inventory;
                $inventory->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_inventory->formulir->form_date));
                $inventory->formulir_id = $cut_off_inventory->formulir_id;
                $inventory->warehouse_id = $cut_off_inventory_detail->warehouse_id;
                $inventory->item_id = $cut_off_inventory_detail->subledger_id;
                $inventory->quantity = $cut_off_inventory_detail->stock;
                $inventory->price = $cut_off_inventory_detail->amount / $cut_off_inventory_detail->stock;

                $inventory_helper = new InventoryHelper($inventory);
                $inventory_helper->in();

                // CUTOFF JOURNAL
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_inventory_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $cut_off_inventory_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_inventory_detail->subledger_id;
                $journal->subledger_type = $cut_off_inventory_detail->subledger_type;
                $journal->save();

                $total += $journal->$position;
            }
        }
        
        $total ? \Log::info('-------- total :'. $total) : '';
    }

    private static function accountFixedAsset($cut_off_account, $cut_off_account_detail)
    {
        $cut_off_fixed_assets = CutOffFixedAssets::joinFormulir()
            ->approvalApproved()
            ->close()
            ->notArchived()
            ->where('form_date', 'like', date('Y-m-d', strtotime($cut_off_account->formulir->form_date)) . '%')
            ->selectOriginal()
            ->orderby('id', 'desc')
            ->first();

        if ($cut_off_fixed_assets) {
            \Log::info('journal FA started');
            foreach ($cut_off_fixed_assets->cutOffFixedAssetsDetail->where('coa_id', $cut_off_account_detail->coa_id) as $cut_off_fixed_assets_detail) {
                $position = $cut_off_account_detail->debit ? 'debit' : 'credit';
                
                \Log::info('journal FA -' .$cut_off_fixed_assets_detail->coa->account . ' '. $position. ' ' . $cut_off_fixed_assets_detail->total_price);
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_fixed_assets_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->$position = $cut_off_fixed_assets_detail->total_price;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_fixed_assets_detail->subledger_id;
                $journal->subledger_type = get_class( new FixedAsset());
                $journal->save();
            }
        }
    }
}
