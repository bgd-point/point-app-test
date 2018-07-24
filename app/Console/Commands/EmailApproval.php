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
    protected $signature = 'dev:resend-email';

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

        $this->line(count($formulirs) . " form(s) found. Resending email...");

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
            }
        }
        if(count($purchasing_service_invoice) > 0) {
            \Point\PointPurchasing\Http\Controllers\Service\InvoiceApprovalController::
                sendingRequestApproval($purchasing_service_invoice);
            $this->line("Point\PointPurchasing\Models\Service\Invoice " . count($purchasing_service_invoice) . " form(s) resent.");
        }
        if(count($purchasing_service_downpayment) > 0) {
            \Point\PointPurchasing\Http\Controllers\Service\DownpaymentApprovalController::
                sendingRequestApproval($purchasing_service_downpayment);
            $this->line("Point\PointPurchasing\Models\Service\Downpayment " . count($purchasing_service_downpayment) . " form(s) resent.");
        }
        if(count($purchasing_service_payment_order) > 0) {
            \Point\PointPurchasing\Http\Controllers\Service\PaymentOrderApprovalController::
                sendingRequestApproval($purchasing_service_payment_order);
            $this->line("Point\PointPurchasing\Models\Service\PaymentOrder " . count($purchasing_service_payment_order) . " form(s) resent.");
        }

        if(count($purchasing_goods_purchase_requisition) > 0) {
            \Point\PointPurchasing\Http\Controllers\Inventory\PurchaseRequisitionApprovalController::
                sendingRequestApproval($purchasing_goods_purchase_requisition);
            $this->line("Point\PointPurchasing\Models\Inventory\PurchaseRequisition " . count($purchasing_goods_purchase_requisition) . " form(s) resent.");
        }
        if(count($purchasing_goods_purchase_order) > 0) {
            \Point\PointPurchasing\Http\Controllers\Inventory\PurchaseOrderApprovalController::
                sendingRequestApproval($purchasing_goods_purchase_order);
            $this->line("Point\PointPurchasing\Models\Inventory\PurchaseOrder " . count($purchasing_goods_purchase_order) . " form(s) resent.");
        }
        if(count($purchasing_goods_downpayment) > 0) {
            \Point\PointPurchasing\Http\Controllers\Inventory\DownpaymentApprovalController::
                sendingRequestApproval($purchasing_goods_downpayment);
            $this->line("Point\PointPurchasing\Models\Inventory\Downpayment " . count($purchasing_goods_downpayment) . " form(s) resent.");
        }
        if(count($purchasing_goods_payment_order) > 0) {
            \Point\PointPurchasing\Http\Controllers\Inventory\PaymentOrderApprovalController::
                sendingRequestApproval($purchasing_goods_payment_order);
            $this->line("Point\PointPurchasing\Models\Inventory\PaymentOrder " . count($purchasing_goods_payment_order) . " form(s) resent.");
        }

        if(count($inventory_inventory_usage) > 0) {
            \Point\PointInventory\Http\Controllers\InventoryUsage\InventoryUsageApprovalController::
                sendingRequestApproval($inventory_inventory_usage);
            $this->line("Point\PointInventory\Models\InventoryUsage\InventoryUsage " . count($inventory_inventory_usage) . " form(s) resent.");
        }
        if(count($inventory_stock_correction) > 0) {
            \Point\PointInventory\Http\Controllers\StockCorrection\StockCorrectionApprovalController::   
                sendingRequestApproval($inventory_stock_correction);
            $this->line("Point\PointInventory\Models\StockCorrection\StockCorrection " . count($inventory_stock_correction) . " form(s) resent.");
        }
        if(count($inventory_transfer_item) > 0) {
            \Point\PointInventory\Http\Controllers\TransferItem\TransferItemApprovalController::
                sendingRequestApproval($inventory_transfer_item);
            $this->line("Point\PointInventory\Models\TransferItem\TransferItem " . count($inventory_transfer_item) . " form(s) resent.");
        }
        if(count($inventory_stock_opname) > 0) {
            \Point\PointInventory\Http\Controllers\StockOpname\StockOpnameApprovalController::
                sendingRequestApproval($inventory_stock_opname);
            $this->line("Point\PointInventory\Models\StockOpname\StockOpname " . count($inventory_stock_opname) . " form(s) resent.");
        }
    }
}
