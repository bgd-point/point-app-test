<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTransaction extends Command
{
    protected $signature = 'dev:clear-transactions';
    protected $description = 'Remove all transaction data but keep master data';

    public function handle()
    {
        $this->warn('⚠ This will delete all transaction data!');
        if (!$this->confirm('Are you sure you want to proceed?')) {
            $this->info('Cancelled.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $transactionTables = [
            'account_depreciation',
            'account_payable_and_receivable',
            'account_payable_and_receivable_detail',
            'allocation_report',
            'bumi_deposit',
            'bumi_deposit_bank',
            'bumi_deposit_bank_account',
            'bumi_deposit_bank_product',
            'bumi_deposit_category',
            'bumi_deposit_group',
            'bumi_deposit_owner',
            'bumi_deposit_pph',
            'bumi_shares',
            'bumi_shares_broker',
            'bumi_shares_buy',
            'bumi_shares_owner',
            'bumi_shares_owner_group',
            'bumi_shares_sell',
            'bumi_shares_selling_price',
            'bumi_shares_stock',
            'bumi_shares_stock_fifo',
            'email_history',
            'failed_jobs',
            'fixed_asset',
            'fixed_assets_contract',
            'fixed_assets_contract_detail',
            'fixed_assets_contract_reference',
            'formulir',
            'formulir_lock',
            'formulir_number',
            'history',
            'inventory',
            'jobs',
            'journal',
            'ksp_loan_application',
            'opening_inventory',
            'password_resets',
            'point_accounting_cut_off_account',
            'point_accounting_cut_off_account_detail',
            'point_accounting_cut_off_fixed_assets',
            'point_accounting_cut_off_fixed_assets_detail',
            'point_accounting_cut_off_inventory',
            'point_accounting_cut_off_inventory_detail',
            'point_accounting_cut_off_payable',
            'point_accounting_cut_off_payable_detail',
            'point_accounting_cut_off_receivable',
            'point_accounting_cut_off_receivable_detail',
            'point_accounting_memo_journal',
            'point_accounting_memo_journal_detail',
            'point_expedition_downpayment',
            'point_expedition_invoice',
            'point_expedition_invoice_item',
            'point_expedition_order',
            'point_expedition_order_item',
            'point_expedition_order_reference',
            'point_expedition_order_reference_item',
            'point_expedition_payment_order',
            'point_expedition_payment_order_detail',
            'point_expedition_payment_order_other',
            'point_finance_bank',
            'point_finance_bank_detail',
            'point_finance_cash',
            'point_finance_cash_advance',
            'point_finance_cash_cash_advance',
            'point_finance_cash_detail',
            'point_finance_payment_order',
            'point_finance_payment_order_detail',
            'point_finance_payment_reference',
            'point_finance_payment_reference_detail',
            'point_inventory_stock_correction',
            'point_inventory_stock_correction_item',
            'point_inventory_stock_opname',
            'point_inventory_stock_opname_item',
            'point_inventory_transfer_item',
            'point_inventory_transfer_item_detail',
            'point_inventory_usage',
            'point_inventory_usage_item',
            'point_manufacture_formula',
            'point_manufacture_formula_material',
            'point_manufacture_formula_product',
            'point_manufacture_input',
            'point_manufacture_input_material',
            'point_manufacture_input_product',
            'point_manufacture_machine',
            'point_manufacture_output',
            'point_manufacture_output_material',
            'point_manufacture_output_product',
            'point_manufacture_process',
            'point_purchasing_cash_advance',
            'point_purchasing_downpayment',
            'point_purchasing_fixed_assets_downpayment',
            'point_purchasing_fixed_assets_goods_received',
            'point_purchasing_fixed_assets_goods_received_detail',
            'point_purchasing_fixed_assets_invoice',
            'point_purchasing_fixed_assets_invoice_detail',
            'point_purchasing_fixed_assets_order',
            'point_purchasing_fixed_assets_order_detail',
            'point_purchasing_fixed_assets_payment_order',
            'point_purchasing_fixed_assets_payment_order_detail',
            'point_purchasing_fixed_assets_payment_order_other',
            'point_purchasing_fixed_assets_requisition',
            'point_purchasing_fixed_assets_requisition_detail',
            'point_purchasing_fixed_assets_retur',
            'point_purchasing_fixed_assets_retur_detail',
            'point_purchasing_goods_received',
            'point_purchasing_goods_received_item',
            'point_purchasing_invoice',
            'point_purchasing_invoice_item',
            'point_purchasing_order',
            'point_purchasing_order_item',
            'point_purchasing_payment_order',
            'point_purchasing_payment_order_detail',
            'point_purchasing_payment_order_other',
            'point_purchasing_requisition',
            'point_purchasing_requisition_item',
            'point_purchasing_retur',
            'point_purchasing_retur_item',
            'point_purchasing_service_downpayment',
            'point_purchasing_service_invoice',
            'point_purchasing_service_invoice_item',
            'point_purchasing_service_invoice_service',
            'point_purchasing_service_payment_order',
            'point_purchasing_service_payment_order_detail',
            'point_purchasing_service_payment_order_other',
            'point_purchasing_service_purchase_order',
            'point_purchasing_service_purchase_order_detail',
            'point_sales_delivery_order',
            'point_sales_delivery_order_item',
            'point_sales_downpayment',
            'point_sales_invoice',
            'point_sales_invoice_item',
            'point_sales_order',
            'point_sales_order_item',
            'point_sales_payment_collection',
            'point_sales_payment_collection_detail',
            'point_sales_payment_collection_other',
            'point_sales_pos',
            'point_sales_pos_item',
            'point_sales_pos_pricing',
            'point_sales_pos_pricing_item',
            'point_sales_pos_retur',
            'point_sales_pos_retur_item',
            'point_sales_quotation',
            'point_sales_quotation_item',
            'point_sales_retur',
            'point_sales_retur_item',
            'point_sales_service_downpayment',
            'point_sales_service_invoice',
            'point_sales_service_invoice_item',
            'point_sales_service_invoice_service',
            'point_sales_service_payment_collection',
            'point_sales_service_payment_collection_detail',
            'point_sales_service_payment_collection_other',
            'refer',
            'temp',
            'timeline',
            'vesa'
            ];

        foreach ($transactionTables as $table) {
            try {
                DB::table($table)->truncate();
                $this->info("✅ Truncated: {$table}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to truncate {$table}: " . $e->getMessage());
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('🎉 All transaction data cleared successfully.');
    }
}
