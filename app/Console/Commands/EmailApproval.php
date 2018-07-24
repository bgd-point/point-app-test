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
     * made by someone before
     *
     * @return mixed
     */
    // public function handle()
    // {
    //     $list_formulir = Formulir::where('form_status', 0)->where('approval_status', 0)->groupBy('formulirable_type')->get();
    //     foreach ($list_formulir as $formulir) {
    //         $this->sendEmail($formulir->formulirable_type);
    //     }
    // }

    // private function sendEmail($class)
    // {
    //     $formulir_open = [];
    //     $list_formulir = Formulir::where('form_status', 0)->where('approval_status', 0)->where('formulirable_type', $class)->get();
    //     foreach ($list_formulir as $formulir) {
    //         array_push($formulir_open, $formulir->id);
    //     }

    //     $list_approver = $class::selectApproverList($formulir_open);
    //     $token = md5(date('ymdhis'));
    //     foreach ($list_approver as $data_approver) {
    //         $list_data = $class::selectApproverRequest($formulir_open, $data_approver->approval_to);
            
    //         $array_formulir_id = [];
    //         foreach ($list_data as $data) {
    //             array_push($array_formulir_id, $data->formulir_id);
    //         }

    //         $array_formulir_id = implode(',', $array_formulir_id);
    //         $approver = User::find($data_approver->approval_to);
    //         $data = [
    //             'list_data' => $list_data,
    //             'token' => $token,
    //             'username' => 'this email by System',
    //             'url' => '//' . env('SERVER_DOMAIN'),
    //             'approver' => $approver,
    //             'array_formulir_id' => $array_formulir_id
    //             ];

    //         \Mail::send($class::bladeEmail(), $data, function ($message) use ($approver) {
    //             $message->to($approver->email)->subject('request approval #' . date('ymdHi'));
    //         });
            
    //         foreach ($list_data as $data) {
    //             formulir_update_token($data->formulir, $token);
    //         }
    //     }
    // }


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
            // ->whereRaw('request_approval_at < CURDATE()') //form has been requested approval more than 1 day ago
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

        $sales_goods_quotation = [];
        $sales_goods_sales_order = [];
        $sales_goods_downpayment = [];
        $sales_goods_delivery_order = [];
        $sales_goods_invoice = [];
        $sales_goods_payment_collection = [];
        // $sales_goods_retur = [];
        $sales_service_invoice = [];
        $sales_service_payment_collection = [];


        foreach($formulirs AS $key=>$formulir) {
            // $this->line($key . ". " . $formulir->formulirable_type . " " . $formulir->request_approval_at . " | " . $formulir->form_status . " | " . $formulir->approval_status . " | " . $formulir->cancel_requested_at);
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
                case "Point\PointSales\Models\Sales\Invoice":
                    array_push($sales_goods_invoice, $formulir->id);
                    break;
                case "Point\PointSales\Models\Sales\PaymentCollection":
                    array_push($sales_goods_payment_collection, $formulir->id);
                    break;
                case "Point\PointSales\Models\Service\Invoice":
                    array_push($sales_service_invoice, $formulir->id);
                    break;
                case "Point\PointSales\Models\Service\PaymentCollection":
                    array_push($sales_service_payment_collection, $formulir->id);
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

        if(count($sales_goods_quotation) > 0) {
            \Point\PointSales\Http\Controllers\Sales\SalesQuotationApprovalController::
                sendingRequestApproval($sales_goods_quotation);
            $this->line("Point\PointSales\Models\Sales\SalesQuotation " . count($sales_goods_quotation) . " form(s) resent.");
        }
        if(count($sales_goods_sales_order) > 0) {
            \Point\PointSales\Http\Controllers\Sales\SalesOrderApprovalController::
                sendingRequestApproval($sales_goods_sales_order);
            $this->line("Point\PointSales\Models\Sales\SalesOrder " . count($sales_goods_sales_order) . " form(s) resent.");
        }
        if(count($sales_goods_downpayment) > 0) {
            \Point\PointSales\Http\Controllers\Sales\DownpaymentApprovalController::
                sendingRequestApproval($sales_goods_downpayment);
            $this->line("Point\PointSales\Models\Sales\Downpayment " . count($sales_goods_downpayment) . " form(s) resent.");
        }
        // if(count($sales_goods_delivery_order) > 0) {
        //         sendingRequestApproval($sales_goods_delivery_order);
        //     $this->line("Point\PointSales\Models\Sales\DeliveryOrder " . count($sales_goods_delivery_order) . " form(s) resent.");
        // }
        // if(count($sales_goods_invoice) > 0) {
        //         sendingRequestApproval($sales_goods_invoice);
        //     $this->line("Point\PointSales\Models\Sales\Invoice " . count($sales_goods_invoice) . " form(s) resent.");
        // }
        // if(count($sales_goods_payment_collection) > 0) {
        //         sendingRequestApproval($sales_goods_payment_collection);
        //     $this->line("Point\PointSales\Models\Sales\PaymentCollection " . count($sales_goods_payment_collection) . " form(s) resent.");
        // }
        // if(count($sales_goods_retur) > 0) {
        //         sendingRequestApproval($sales_goods_retur);
        //     $this->line( . count($sales_goods_retur) . " form(s) resent.");
        // }
        // if(count($sales_service_invoice) > 0) {
        //         sendingRequestApproval($sales_service_invoice);
        //     $this->line("Point\PointSales\Models\Service\Invoice " . count($sales_service_invoice) . " form(s) resent.");
        // }
        // if(count($sales_service_payment_collection) > 0) {
        //         sendingRequestApproval($sales_service_payment_collection);
        //     $this->line("Point\PointSales\Models\Service\PaymentCollection " . count($sales_service_payment_collection) . " form(s) resent.");
        // }
        // dd($inventory_transfer_item);
    }
}
// "Point\Framework\Models\OpeningInventory"
// "Point\PointAccounting\Models\CutOffAccount"
// "Point\PointAccounting\Models\CutOffInventory"
// "Point\PointAccounting\Models\MemoJournal"

// "Point\PointExpedition\Models\Downpayment"
// "Point\PointExpedition\Models\ExpeditionOrder"
// "Point\PointExpedition\Models\Invoice"
// "Point\PointExpedition\Models\PaymentOrder"

// "Point\PointFinance\Models\Bank\Bank"
// "Point\PointFinance\Models\Cash\Cash"
// "Point\PointFinance\Models\CashAdvance"
// "Point\PointFinance\Models\PaymentOrder\PaymentOrder"

// "Point\PointInventory\Models\InventoryUsage\InventoryUsage"
// "Point\PointInventory\Models\StockCorrection\StockCorrection"
// "Point\PointInventory\Models\TransferItem\TransferItem"
// "Point\PointInventory\Models\StockOpname\StockOpname"

// "Point\PointManufacture\Models\Formula"
// "Point\PointManufacture\Models\InputProcess"
// "Point\PointManufacture\Models\OutputProcess"

// "Point\PointPurchasing\Models\Inventory\Downpayment"
// "Point\PointPurchasing\Models\Inventory\GoodsReceived"
// "Point\PointPurchasing\Models\Inventory\Invoice"
// "Point\PointPurchasing\Models\Inventory\PaymentOrder"
// "Point\PointPurchasing\Models\Inventory\PurchaseOrder"
// "Point\PointPurchasing\Models\Inventory\PurchaseRequisition"

// "Point\PointSales\Models\Sales\SalesQuotation"
// "Point\PointSales\Models\Sales\SalesOrder"
// "Point\PointSales\Models\Sales\Downpayment"
// "Point\PointSales\Models\Sales\DeliveryOrder"
// "Point\PointSales\Models\Sales\Invoice"
// "Point\PointSales\Models\Sales\PaymentCollection"
// "Point\PointSales\Models\Service\Invoice"
// "Point\PointSales\Models\Service\PaymentCollection"
