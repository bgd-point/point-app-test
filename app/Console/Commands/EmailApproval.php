<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Core\Models\User;
use Point\Framework\Models\Formulir;

class EmailApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:resend-email {domain}';

    private $domain;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend form approval request email that is sent yesterday but has not been approved.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * made by Christ Jul 23, 2018
     *
     * @return mixed
     */
    public function handle() {
        $this->line("Counting unapproved forms...");

        $formulirs = Formulir::where('approval_status', 0) // form is still pending (not approved or rejected)
            ->where('form_status', 0) // form is still oopen (not closed / cancelled)
            ->whereRaw('request_approval_at < CURDATE()') //form has been requested approval more than 1 day ago
            ->whereNotNull('request_approval_at') // form has been requested approval before
            ->whereNotNull('form_number') // form not archived
            ->whereNull('cancel_requested_at') // form not asked for cancellation
            ->orderBy('formulirable_type')
            ->get();

        $this->domain = "//" . $this->argument('domain') . "." . ENV('SERVER_DOMAIN') . "/";
        if(count($formulirs) > 1)
            $this->line(count($formulirs) . " forms found. Resending email for " . $this->argument('domain'));
        elseif(count($formulirs) == 1)
            $this->line(count($formulirs) . " form found. Resending email for " . $this->argument('domain'));
        elseif(count($formulirs) == 0)
            $this->line(count($formulirs) . " forms found.");

        $purchasing_service_invoice = [];
        $purchasing_service_downpayment = [];
        $purchasing_service_payment_order = [];

        $purchasing_goods_purchase_requisition = [];
        $purchasing_goods_purchase_order = [];
        $purchasing_goods_downpayment = [];
        $purchasing_goods_payment_order = [];

        $inventory_inventory_usage = [];
        $inventory_stock_correction = [];
        $inventory_transfer_item = [];
        $inventory_stock_opname = [];

        $sales_goods_quotation = [];
        $sales_goods_sales_order = [];
        $sales_goods_downpayment = [];
        $sales_goods_delivery_order = [];
        $sales_goods_payment_collection = [];

        $sales_service_downpayment = [];
        $sales_service_payment_collection = [];

        $finance_cash_advance = [];
        $finance_payment_order = [];

        $expedition_expedition_order = [];
        $expedition_downpayment = [];
        $expedition_payment_order = [];

        $manufacture_formula = [];
        $manufacture_process_in = [];

        $accounting_memo_journal = [];
        $accounting_cutoff_account = [];
        $accounting_cutoff_inventory = [];
        $accounting_cutoff_payable = [];
        $accounting_cutoff_receivable = [];
        $accounting_cutoff_fixed_assets = [];

        foreach($formulirs AS $key=>$formulir) {
            switch($formulir->formulirable_type) {
                case "Point\PointPurchasing\Models\Service\Invoice":
                    array_push($purchasing_service_invoice, $formulir->id);
                    break;
                case "Point\PointPurchasing\Models\Service\Downpayment":
                    array_push($purchasing_service_downpayment, $formulir->id);
                    break;
                case "Point\PointPurchasing\Models\Service\PaymentOrder":
                    array_push($purchasing_service_payment_order, $formulir->id);
                    break;

                case "Point\PointPurchasing\Models\Inventory\PurchaseRequisition":
                    array_push($purchasing_goods_purchase_requisition, $formulir->id);
                    break;
                case "Point\PointPurchasing\Models\Inventory\PurchaseOrder":
                    array_push($purchasing_goods_purchase_order, $formulir->id);
                    break;
                case "Point\PointPurchasing\Models\Inventory\Downpayment":
                    array_push($purchasing_goods_downpayment, $formulir->id);
                    break;
                case "Point\PointPurchasing\Models\Inventory\PaymentOrder":
                    array_push($purchasing_goods_payment_order, $formulir->id);
                    break;

                case "Point\PointInventory\Models\InventoryUsage\InventoryUsage":
                    array_push($inventory_inventory_usage, $formulir->id);
                    break;
                case "Point\PointInventory\Models\StockCorrection\StockCorrection":
                    array_push($inventory_stock_correction, $formulir->id);
                    break;
                case "Point\PointInventory\Models\TransferItem\TransferItem":
                    array_push($inventory_transfer_item, $formulir->id);
                    break;
                case "Point\PointInventory\Models\StockOpname\StockOpname":
                    array_push($inventory_stock_opname, $formulir->id);
                    break;

                case "Point\PointSales\Models\Sales\SalesQuotation":
                    array_push($sales_goods_quotation, $formulir->id);
                    break;
                case "Point\PointSales\Models\Sales\SalesOrder":
                    array_push($sales_goods_sales_order, $formulir->id);
                    break;
                case "Point\PointSales\Models\Sales\Downpayment":
                    array_push($sales_goods_downpayment, $formulir->id);
                    break;
                case "Point\PointSales\Models\Sales\DeliveryOrder":
                    array_push($sales_goods_delivery_order, $formulir->id);
                    break;
                case "Point\PointSales\Models\Sales\PaymentCollection":
                    array_push($sales_goods_payment_collection, $formulir->id);
                    break;

                case "Point\PointSales\Models\Service\Downpayment":
                    array_push($sales_service_downpayment, $formulir->id);
                    break;
                case "Point\PointSales\Models\Service\PaymentCollection":
                    array_push($sales_service_payment_collection, $formulir->id);
                    break;

                case "Point\PointFinance\Models\CashAdvance":
                    array_push($finance_cash_advance, $formulir->id);
                    break;
                case "Point\PointFinance\Models\PaymentOrder\PaymentOrder":
                    array_push($finance_payment_order, $formulir->id);
                    break;

                case "Point\PointExpedition\Models\ExpeditionOrder":
                    array_push($expedition_expedition_order, $formulir->id);
                    break;
                case "Point\PointExpedition\Models\Downpayment":
                    array_push($expedition_downpayment, $formulir->id);
                    break;
                case "Point\PointExpedition\Models\PaymentOrder":
                    array_push($expedition_payment_order, $formulir->id);
                    break;

                case "Point\PointManufacture\Models\Formula":
                    array_push($manufacture_formula, $formulir->id);
                    break;
                case "Point\PointManufacture\Models\InputProcess":
                    array_push($manufacture_process_in, $formulir->id);
                    break;

                case "Point\PointAccounting\Models\MemoJournal":
                    array_push($accounting_memo_journal, $formulir->id);
                    break;
                case "Point\PointAccounting\Models\CutOffAccount":
                    array_push($accounting_cutoff_account, $formulir->id);
                    break;
                case "Point\PointAccounting\Models\CutOffInventory":
                    array_push($accounting_cutoff_inventory, $formulir->id);
                    break;
                case "Point\PointAccounting\Models\CutOffPayable":
                    array_push($accounting_cutoff_payable, $formulir->id);
                    break;
                case "Point\PointAccounting\Models\CutOffReceivable":
                    array_push($accounting_cutoff_receivable, $formulir->id);
                    break;
                case "Point\PointAccounting\Models\CutOffFixedAssets":
                    array_push($accounting_cutoff_fixed_assets, $formulir->id);
                    break;
            }
        }
        $this->executeSendEmail($purchasing_service_invoice, new \Point\PointPurchasing\Http\Controllers\Service\InvoiceApprovalController, "Purchase/Service/Invoice");
        $this->executeSendEmail($purchasing_service_downpayment, new \Point\PointPurchasing\Http\Controllers\Service\DownpaymentApprovalController, "Purchase/Service/Downpayment");
        $this->executeSendEmail($purchasing_service_payment_order, new \Point\PointPurchasing\Http\Controllers\Service\PaymentOrderApprovalController, "Purchase/Service/PaymentOrder");

        $this->executeSendEmail($purchasing_goods_purchase_requisition, new \Point\PointPurchasing\Http\Controllers\Inventory\PurchaseRequisitionApprovalController, "Purchase/Inventory/PurchaseRequisition");
        $this->executeSendEmail($purchasing_goods_purchase_order, new \Point\PointPurchasing\Http\Controllers\Inventory\PurchaseOrderApprovalController, "Purchase/Inventory/PurchaseOrder");
        $this->executeSendEmail($purchasing_goods_downpayment, new \Point\PointPurchasing\Http\Controllers\Inventory\DownpaymentApprovalController, "Purchase/Inventory/Downpayment");
        $this->executeSendEmail($purchasing_goods_payment_order, new \Point\PointPurchasing\Http\Controllers\Inventory\PaymentOrderApprovalController, "Purchase/Inventory/PaymentOrder");

        $this->executeSendEmail($inventory_inventory_usage, new \Point\PointInventory\Http\Controllers\InventoryUsage\InventoryUsageApprovalController, "Inventory/InventoryUsage");
        $this->executeSendEmail($inventory_stock_correction, new \Point\PointInventory\Http\Controllers\StockCorrection\StockCorrectionApprovalController, "Inventory/StockCorrection");
        $this->executeSendEmail($inventory_transfer_item, new \Point\PointInventory\Http\Controllers\TransferItem\TransferItemApprovalController, "Inventory/TransferItem");
        $this->executeSendEmail($inventory_stock_opname, new \Point\PointInventory\Http\Controllers\StockOpname\StockOpnameApprovalController, "Inventory/StockOpname");

        $this->executeSendEmail($sales_goods_quotation, new \Point\PointSales\Http\Controllers\Sales\SalesQuotationApprovalController, "Sales/Inventory/SalesQuotation");
        $this->executeSendEmail($sales_goods_sales_order, new \Point\PointSales\Http\Controllers\Sales\SalesOrderApprovalController, "Sales/Inventory/SalesOrder");
        $this->executeSendEmail($sales_goods_downpayment, new \Point\PointSales\Http\Controllers\Sales\DownpaymentApprovalController, "Sales/Inventory/Downpayment");
        $this->executeSendEmail($sales_goods_delivery_order, new \Point\PointSales\Http\Controllers\Sales\DeliveryOrderApprovalController, "Sales/Inventory/DeliveryOrder");
        $this->executeSendEmail($sales_goods_payment_collection, new \Point\PointSales\Http\Controllers\Sales\PaymentCollectionApprovalController, "Sales/Inventory/PaymentCollection");

        $this->executeSendEmail($sales_service_downpayment, new \Point\PointSales\Http\Controllers\Service\DownpaymentApprovalController, "Sales/Service/Downpayment");
        $this->executeSendEmail($sales_service_payment_collection, new \Point\PointSales\Http\Controllers\Service\PaymentCollectionApprovalController, "Sales/Service/PaymentCollection");

        $this->executeSendEmail($finance_cash_advance, new \Point\PointFinance\Http\Controllers\CashAdvanceApprovalController, "Finance/CashAdvance");
        $this->executeSendEmail($finance_payment_order, new \Point\PointFinance\Http\Controllers\PaymentOrder\PaymentOrderApprovalController, "Finance/PaymentOrder");

        $this->executeSendEmail($expedition_expedition_order, new \Point\PointExpedition\Http\Controllers\ExpeditionOrderApprovalController, "Expedition/ExpeditionOrder");
        $this->executeSendEmail($expedition_downpayment, new \Point\PointExpedition\Http\Controllers\DownpaymentApprovalController, "Expedition/Downpayment");
        $this->executeSendEmail($expedition_payment_order, new \Point\PointExpedition\Http\Controllers\PaymentOrderApprovalController, "Expedition/PaymentOrder");
        
        $this->executeSendEmail($manufacture_formula, new \Point\PointManufacture\Http\Controllers\FormulaApprovalController, "Manufacture/Formula");
        $this->executeSendEmail($manufacture_process_in, new \Point\PointManufacture\Http\Controllers\InputApprovalController, "Manufacture/InputProcess");

        $this->executeSendEmail($accounting_memo_journal, new \Point\PointAccounting\Http\Controllers\MemoJournalApprovalController, "Accounting/MemoJournal");
        $this->executeSendEmail($accounting_cutoff_account, new \Point\PointAccounting\Http\Controllers\Cutoff\CutOffAccountApprovalController, "Accounting/CutOffAccount");
        $this->executeSendEmail($accounting_cutoff_inventory, new \Point\PointAccounting\Http\Controllers\Cutoff\CutOffInventoryApprovalController, "Accounting/CutOffInventory");
        $this->executeSendEmail($accounting_cutoff_receivable, new \Point\PointAccounting\Http\Controllers\Cutoff\CutOffReceivableApprovalController, "Accounting/CutOffReceivable");
        $this->executeSendEmail($accounting_cutoff_payable, new \Point\PointAccounting\Http\Controllers\Cutoff\CutOffPayableApprovalController, "Accounting/CutOffPayable");
        $this->executeSendEmail($accounting_cutoff_fixed_assets, new \Point\PointAccounting\Http\Controllers\Cutoff\CutOffFixedAssetsApprovalController, "Accounting/CutOffFixedAssets");
    }

    private function executeSendEmail($list_id, $parent, $prefix_output)
    {
        if(count($list_id) > 0) {
            $parent::sendingRequestApproval($list_id, "VESA", $this->domain);
            $this->line($prefix_output . " " . count($list_id) . " form(s) resent.");
        }
    }
}
