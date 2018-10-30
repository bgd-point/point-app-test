<?php

namespace App\Http\Controllers;

use Point\Framework\Vesa\MasterVesa;
use Point\PointAccounting\Models\MemoJournal;
use Point\PointExpedition\Models\Downpayment as DownpaymentExpedition;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\Invoice as InvoiceExpedition;
use Point\PointExpedition\Models\PaymentOrder as PaymentOrderExpedition;
use Point\PointFinance\Models\PaymentOrder\PaymentOrder as PaymentOrderFinance;
use Point\PointFinance\Models\PaymentReference;
use Point\PointInventory\Models\InventoryUsage\InventoryUsage;
use Point\PointInventory\Models\StockCorrection\StockCorrection;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointManufacture\Models\InputProcess;
use Point\PointPurchasing\Models\Inventory\CashAdvance;
use Point\PointPurchasing\Models\Inventory\Downpayment as DownpaymentPurchasing;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\Invoice as InvoicePurchasing;
use Point\PointPurchasing\Models\Inventory\PaymentOrder as PaymentOrderPurchasing;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;
use Point\PointPurchasing\Models\Service\Downpayment as PurchaseServiceDownpayment;
use Point\PointPurchasing\Models\Service\PaymentOrder as PurchaseServicePaymentOrder;
use Point\PointPurchasing\Models\Service\Invoice as PurchaseServiceInvoice;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\Downpayment as DownpaymentSales;
use Point\PointSales\Models\Sales\Invoice as InvoiceSales;
use Point\PointSales\Models\Sales\PaymentCollection as SalesPaymentCollection;
use Point\PointSales\Models\Sales\Retur as ReturSales;
use Point\PointSales\Models\Sales\SalesOrder;
use Point\PointSales\Models\Sales\SalesQuotation;
use Point\PointSales\Models\Service\Downpayment as SalesServiceDownpayment;
use Point\PointSales\Models\Service\PaymentCollection as ServicePaymentCollection;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\AllocationReport;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Helpers\JournalHelper;

class DashboardController extends Controller
{
    public function index()
    {
        $view = view('app.index');

        $array_vesa = [];
        $array_vesa_payment = [];

        /**
         * add additional vesa here
         *
         * $array_vesa = array_merge($array_vesa, YourPackage::getVesa());
         * $array_vesa = array_merge($array_vesa, OtherPackage::getVesa());
         */

        // FRAMEWORK
        $array_vesa = array_merge($array_vesa, MasterVesa::getVesa());
        
        // INVENTORY
        $array_vesa = array_merge($array_vesa, InventoryUsage::getVesa());
        $array_vesa = array_merge($array_vesa, StockCorrection::getVesa());
        $array_vesa = array_merge($array_vesa, StockOpname::getVesa());
        $array_vesa = array_merge($array_vesa, TransferItem::getVesa());

        // SALES
        $array_vesa = array_merge($array_vesa, SalesQuotation::getVesa());
        $array_vesa = array_merge($array_vesa, SalesOrder::getVesa());
        $array_vesa = array_merge($array_vesa, DeliveryOrder::getVesa());
        $array_vesa = array_merge($array_vesa, DownpaymentSales::getVesa());
        $array_vesa = array_merge($array_vesa, InvoiceSales::getVesa());
        $array_vesa = array_merge($array_vesa, ReturSales::getVesa());
        $array_vesa = array_merge($array_vesa, SalesPaymentCollection::getVesa());
        $array_vesa = array_merge($array_vesa, ServicePaymentCollection::getVesa());
        $array_vesa = array_merge($array_vesa, SalesServiceDownpayment::getVesa());

        // PURCHASING INVENTORY
        $array_vesa = array_merge($array_vesa, PurchaseRequisition::getVesa());
        $array_vesa = array_merge($array_vesa, PurchaseOrder::getVesa());
        $array_vesa = array_merge($array_vesa, DownpaymentPurchasing::getVesa());
        $array_vesa = array_merge($array_vesa, GoodsReceived::getVesa());
        $array_vesa = array_merge($array_vesa, InvoicePurchasing::getVesa());
        $array_vesa = array_merge($array_vesa, PaymentOrderPurchasing::getVesa());
//        $array_vesa = array_merge($array_vesa, CashAdvance::getVesa());

        // PURCHASING SERVICE
        $array_vesa = array_merge($array_vesa, PurchaseServiceInvoice::getVesa());
        $array_vesa = array_merge($array_vesa, PurchaseServicePaymentOrder::getVesa());
        $array_vesa = array_merge($array_vesa, PurchaseServiceDownpayment::getVesa());

        // EXPEDITION
        $array_vesa = array_merge($array_vesa, ExpeditionOrder::getVesa());
        $array_vesa = array_merge($array_vesa, InvoiceExpedition::getVesa());
        $array_vesa = array_merge($array_vesa, DownpaymentExpedition::getVesa());
        $array_vesa = array_merge($array_vesa, PaymentOrderExpedition::getVesa());

        // MANUFACTURE
        $array_vesa = array_merge($array_vesa, InputProcess::getVesa());

        // FINANCE
        $array_vesa = array_merge($array_vesa, PaymentOrderFinance::getVesa());
        $array_vesa_payment = array_merge($array_vesa_payment, PaymentReference::getVesa());
        $array_vesa = array_merge($array_vesa, \Point\PointFinance\Models\CashAdvance::getVesa());

        // ACCOUNTING
        $array_vesa = array_merge($array_vesa, MemoJournal::getVesa());

        $view->array_vesa = $array_vesa;
        $view->array_vesa_payment = $array_vesa_payment;

        return $view;
    }

    public function syncBudget() {
        $allocation = $this->getAllocation();

        $coa = $this->getCoaBalance();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://kbretail.cloud.point.red/api/v1/sync-budget",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([$allocation, $coa]),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "content-type: application/json",
                "Authorization: Bearer token",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return Response()->json($err, 400);
        }
        
        return Response()->json($response);
        
    }

    private function getAllocation() {
        return AllocationReport::joinAllocation()
            ->joinFormulir()
            ->notCanceled()
            ->notArchived()
            ->selectOriginal()
            ->addSelect('formulir.form_date', 'allocation.name')
            ->get();
    }

    private function getCoaBalance() {
        $coa = Coa::orderBy('coa_number')
            ->orderBy('name')
            ->select(
                'id',
                'coa_category_id',
                'coa_group_id',
                'name',
                'coa_number',
                'notes'
            )
            ->get();
        // attach ending balance
        foreach($coa as $value) {
            $value->balance = JournalHelper::coaEndingBalance($value->id, date('Y-m-d 23:59:59'));
        }

        return $coa;
    }
}
