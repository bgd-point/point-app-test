<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\AccountPayableAndReceivable;
use Point\Framework\Models\FixedAsset;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffInventory;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffReceivable;

class FixSeederCutoff extends Seeder
{
    public function run()
    {
        \DB::beginTransaction();
        $list_cutoff_account = CutOffAccount::joinFormulir()->close()->notArchived()->approvalApproved()->selectOriginal()->get();
        foreach ($list_cutoff_account as $cutoff_account) {
            $journal = Journal::where('form_journal_id', $cutoff_account->formulir_id )->selectRaw('sum(debit) as debit, sum(credit) as credit')->first();
            \Log::info('before :: debit = ' . $journal->debit. ' credit = '.$journal->credit);
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
                        self::accountFixedAsset($cut_off_account, $cut_off_account_detail);
                    }
	            }
        	}
            $journal = Journal::where('form_journal_id', $cutoff_account->formulir_id )->selectRaw('sum(debit) as debit, sum(credit) as credit')->first();
            \Log::info('after :: debit = ' . $journal->debit. ' credit = '.$journal->credit);
        }
        \DB::commit();
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

        if ($cut_off_payable) {
            foreach($cut_off_payable->cutOffPayableDetail as $cut_off_payable_detail) {
            	$account_payable = AccountPayableAndReceivable::where('reference_type', get_class($cut_off_payable_detail))->where('reference_id', $cut_off_payable_detail->id)->first();
            	if ($account_payable) {
                    \Log::info('journal Account Payable continue '. $cut_off_payable_detail->coa->name);
            		continue;
            	}

                \Log::info('journal account payable started');
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_payable_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->debit = 0;
                $journal->credit = $cut_off_payable_detail->amount;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_payable_detail->subledger_id;
                $journal->subledger_type = $cut_off_payable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_payable_detail), 'reference_id' => $cut_off_payable_detail->id]);
            }
        }
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

        if ($cut_off_receivable) {
            foreach($cut_off_receivable->cutOffReceivableDetail as $cut_off_receivable_detail) {
            	$account_receivable = AccountPayableAndReceivable::where('reference_type', get_class($cut_off_receivable_detail))->where('reference_id', $cut_off_receivable_detail->id)->first();
            	if ($account_receivable) {
                    \Log::info('journal Account Receivable continue '. $cut_off_receivable_detail->coa->name);
            		continue;
            	}

                \Log::info('journal account receivable started');
                $journal = new Journal();
                $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                $journal->coa_id = $cut_off_receivable_detail->coa_id;
                $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                $journal->debit = $cut_off_receivable_detail->amount;
                $journal->credit = 0;
                $journal->form_journal_id = $cut_off_account->formulir_id;
                $journal->form_reference_id;
                $journal->subledger_id = $cut_off_receivable_detail->subledger_id;
                $journal->subledger_type = $cut_off_receivable_detail->subledger_type;
                $journal->save(['reference_type' => get_class($cut_off_receivable_detail), 'reference_id' => $cut_off_receivable_detail->id]);
            }
        }
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

        // CUTOFF INVENTORY SUBLEDGER
        foreach($cut_off_inventory->cutOffInventoryDetail as $cut_off_inventory_detail) {
            // EMPTY JOURNAL
            $coa_value = JournalHelper::getTotalValueBySubledger($cut_off_inventory_detail->coa_id, $cut_off_account->formulir->form_date, $cut_off_inventory_detail->subledger_type, $cut_off_inventory_detail->subledger_id);
            if ($cut_off_inventory_detail->stock > 0 && $cut_off_inventory_detail->amount > 0) {
                $journal = Journal::where('form_journal_id', $cut_off_account->formulir_id)->where('coa_id', $cut_off_inventory_detail->coa_id)->first();
                if ($journal) {
                    \Log::info('journal inventory continue '. $cut_off_inventory_detail->coa->name);
                    continue;
                }
                \Log::info('journal inventory started');
                $position = JournalHelper::position($cut_off_inventory_detail->coa_id);
                if ($coa_value->debit > 0 || $coa_value->credit > 0) {
                    $journal = new Journal();
                    $journal->form_date = date('Y-m-d 23:59:59', strtotime($cut_off_account->formulir->form_date));
                    $journal->coa_id = $cut_off_inventory_detail->coa_id;
                    $journal->description = "Cut Off from formulir number ".$cut_off_account->formulir->form_number;
                    $journal->debit = $coa_value->credit;
                    $journal->credit = $coa_value->debit;
                    $journal->form_journal_id = $cut_off_account->formulir_id;
                    $journal->form_reference_id;
                    $journal->subledger_id = $cut_off_inventory_detail->subledger_id;
                    $journal->subledger_type = $cut_off_inventory_detail->subledger_type;
                    $journal->save();
                }

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
            }
        }
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
            foreach ($cut_off_fixed_assets->cutOffFixedAssetsDetail as $cut_off_fixed_assets_detail) {
                $journal = Journal::where('form_journal_id', $cut_off_account->formulir_id)->where('coa_id', $cut_off_fixed_assets_detail->coa_id)->first();
                if ($journal) {
                    \Log::info('journal FA continue '. $cut_off_fixed_assets_detail->coa->name);
                    continue;
                }
                \Log::info('journal FA started');
                $position = JournalHelper::position($cut_off_account_detail->coa_id);
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
