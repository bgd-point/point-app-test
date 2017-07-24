<?php

use Illuminate\Database\Seeder;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Models\Master\CoaCategory;
use Point\Framework\Models\Master\CoaGroup;
use Point\Framework\Models\Master\CoaGroupCategory;
use Point\Framework\Models\Master\CoaPosition;
use Point\Framework\Models\AccountDepreciation;

class FrameworkCoaTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Assets, Liability, Equity, Expense, Revenue
        if (! CoaPosition::exist('Assets')) {
            DB::table('coa_position')->insert([ 'name' => 'Assets', 'debit' => true ]);
        }
        if (! CoaPosition::exist('Liability')) {
            DB::table('coa_position')->insert([ 'name' => 'Liability', 'debit' => false ]);
        }
        if (! CoaPosition::exist('Equity')) {
            DB::table('coa_position')->insert([ 'name' => 'Equity', 'debit' => false ]);
        }
        if (! CoaPosition::exist('Revenue')) {
            DB::table('coa_position')->insert([ 'name' => 'Revenue', 'debit' => false ]);
        }
        if (! CoaPosition::exist('Expense')) {
            DB::table('coa_position')->insert([ 'name' => 'Expense', 'debit' => true ]);
        }

        // Coa Group
        if (! CoaGroupCategory::exist('Current Assets')) {
            DB::table('coa_group_category')->insert([ 'coa_position_id' => 1, 'name' => 'Current Assets & Liabilities' ]);
        }
        if (! CoaGroupCategory::exist('Long Term Assets')) {
            DB::table('coa_group_category')->insert([ 'coa_position_id' => 1, 'name' => 'Long Term Assets' ]);
        }

        // Assets account
        $coa_group_category = CoaGroupCategory::where('name', '=', 'Current Assets & Liabilities')->first();
        if (! $coa_group_category) {
            $coa_group_category = DB::table('coa_group_category')->where('name', 'Current Assets & Liabilities')->first();
        }

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Petty Cash');
        Coa::insert($coa_category->id, 'Petty Cash');

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Bank Account');
        Coa::insert($coa_category->id, 'Bank Account');

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Account Receivable');
        Coa::insert($coa_category->id, 'Account Receivable - Sales', true, 'Point\Framework\Models\Master\Person');
        Coa::insert($coa_category->id, 'Income Tax Receivable');

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Inventories');
        Coa::insert($coa_category->id, 'Raw Material', true, 'Point\Framework\Models\Master\Item');
        Coa::insert($coa_category->id, 'Unfinished Goods', true, 'Point\Framework\Models\Master\Item');
        Coa::insert($coa_category->id, 'Finished Goods', true, 'Point\Framework\Models\Master\Item');
        Coa::insert($coa_category->id, 'Sparepart', true, 'Point\Framework\Models\Master\Item');

        Coa::insert($coa_category->id, 'Work in Process');
        Coa::insert($coa_category->id, 'Inventory in Transit');

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Downpayment of Expense');
        Coa::insert($coa_category->id, 'Purchase Downpayment', true, 'Point\Framework\Models\Master\Person');
        Coa::insert($coa_category->id, 'Expedition Downpayment', true, 'Point\Framework\Models\Master\Person');

        $coa_group_category = CoaGroupCategory::where('name', '=', 'Long Term Assets')->first();
        if (! $coa_group_category) {
            $coa_group_category = DB::table('coa_group_category')->where('name', 'Long Term Assets')->first();
        }

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Fixed Assets');
        Coa::insert($coa_category->id, 'Land', true, 'Point\Framework\Models\FixedAsset');
        $account_fixed_asset = Coa::insert($coa_category->id, 'Buildings', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Buildings');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);
        $account_fixed_asset = Coa::insert($coa_category->id, 'Equipments', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Equipments');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);
        $account_fixed_asset = Coa::insert($coa_category->id, 'Furniture', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Furniture');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);
        $account_fixed_asset = Coa::insert($coa_category->id, 'Vehicles', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Vehicles');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);
        $account_fixed_asset = Coa::insert($coa_category->id, 'Machinery', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Machinery');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);
        $account_fixed_asset = Coa::insert($coa_category->id, 'Electronic', true, 'Point\Framework\Models\FixedAsset');
        $account_depreciation = Coa::insert($coa_category->id, 'Accumulated Depreciation Electronic');
        AccountDepreciation::insert($account_fixed_asset->id, $account_depreciation->id);

        $coa_category = CoaCategory::insert(1, $coa_group_category->id, 'Intangible Assets');
        Coa::insert($coa_category->id, 'Patents');
        Coa::insert($coa_category->id, 'Copyrights');

        /**
         * Liability account
         */
        $coa_category = CoaCategory::insert(2, $coa_group_category->id, 'Current Liability');
        $coa_group = CoaGroup::insert($coa_category->id, 'Account Payable');
        Coa::insert($coa_category->id, 'Account Payable', true, 'Point\Framework\Models\Master\Person', $coa_group->id);
        Coa::insert($coa_category->id, 'Account Payable - Purchasing', true, 'Point\Framework\Models\Master\Person', $coa_group->id);
        Coa::insert($coa_category->id, 'Account Payable - Expedition', true, 'Point\Framework\Models\Master\Person', $coa_group->id);
        Coa::insert($coa_category->id, 'Account Payable - Wages', true, 'Point\Framework\Models\Master\Person', $coa_group->id);
        Coa::insert($coa_category->id, 'Account Payable - Interest');
        Coa::insert($coa_category->id, 'Income Tax Payable');

        $coa_category = CoaCategory::insert(2, $coa_group_category->id, 'Downpayment of Income');
        Coa::insert($coa_category->id, 'Sales Downpayment', true, 'Point\Framework\Models\Master\Person');

        $coa_category = CoaCategory::insert(2, $coa_group_category->id, 'Long Term Liability');

        /**
         * Equity account
         */
        $coa_category = CoaCategory::insert(3, $coa_group_category->id, 'Capital');
        Coa::insert($coa_category->id, 'Owners Equity');
        $coa_category = CoaCategory::insert(3, $coa_group_category->id, 'Profit Loss');
        Coa::insert($coa_category->id, 'Profit Loss');
        $coa_category = CoaCategory::insert(3, $coa_group_category->id, 'Retained Earning');
        Coa::insert($coa_category->id, 'Retained Earning');

        /**
         * Revenue account
         */
        $coa_category = CoaCategory::insert(4, $coa_group_category->id, 'Income From Sales');
        Coa::insert($coa_category->id, 'Sale of Goods');
        Coa::insert($coa_category->id, 'Sales Discount');

        $coa_category = CoaCategory::insert(4, $coa_group_category->id, 'Income From Others');
        Coa::insert($coa_category->id, 'Purchase Discount');
        Coa::insert($coa_category->id, 'Expedition Income');
        Coa::insert($coa_category->id, 'Interest Income');
        Coa::insert($coa_category->id, 'Service Income');

        /**
         * Expense account
         */
        $coa_category = CoaCategory::insert(5, $coa_group_category->id, 'Direct Expenses');
        Coa::insert($coa_category->id, 'Cost of Sales');
        Coa::insert($coa_category->id, 'Service Cost');

        $coa_group = CoaGroup::insert($coa_category->id, 'Expedition Expense');
        Coa::insert($coa_category->id, 'Expedition Cost', false, null, $coa_group->id);
        Coa::insert($coa_category->id, 'Expedition Discount', false, null, $coa_group->id);

        Coa::insert($coa_category->id, 'Inventory Usage');
        Coa::insert($coa_category->id, 'Inventory Differences');

        $coa_category = CoaCategory::insert(5, null, 'Indirect Expenses');

        $coa_category = CoaCategory::insert(5, null, 'Sales Expenses');

        $coa_group = CoaGroup::insert($coa_category->id, 'Marketing Expense');

        Coa::insert($coa_category->id, 'Advertising Expense', false, null, $coa_group->id);
        Coa::insert($coa_category->id, 'Accommodation Expense', false, null, $coa_group->id);
        Coa::insert($coa_category->id, 'Other Marketing Expense', false, null, $coa_group->id);
    }
}
