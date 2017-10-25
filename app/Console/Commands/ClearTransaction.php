<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Refer;
use Point\PointExpedition\Models\Downpayment as ExpeditionDownpayment;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\Invoice as ExpeditionInvoice;
use Point\PointExpedition\Models\InvoiceItem as ExpeditionInvoiceItem;
use Point\PointExpedition\Models\PaymentOrder as ExpeditionPaymentOrder;
use Point\PointExpedition\Models\PaymentOrderDetail as ExpeditionPaymentOrderDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsDownpayment;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceivedDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoice;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsInvoiceDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPaymentOrderDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrder;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseOrderDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisitionDetail;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsRetur;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsReturItem;
use Point\PointPurchasing\Models\Inventory\Downpayment as PurchaseDownpayment;
use Point\PointPurchasing\Models\Inventory\GoodsReceived;
use Point\PointPurchasing\Models\Inventory\GoodsReceivedItem;
use Point\PointPurchasing\Models\Inventory\Invoice as PurchaseInvoice;
use Point\PointPurchasing\Models\Inventory\InvoiceItem as PurchaseInvoiceItem;
use Point\PointPurchasing\Models\Inventory\PaymentOrder as PurchasePaymentOrder;
use Point\PointPurchasing\Models\Inventory\PaymentOrderDetail as PurchasePaymentOrderDetail;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseOrderItem;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisitionItem;
use Point\PointPurchasing\Models\Inventory\Retur as PurchaseRetur;
use Point\PointPurchasing\Models\Inventory\ReturItem as PurchaseReturItem;
use Point\PointPurchasing\Models\Service\Downpayment as PurchaseServiceDownpayment;
use Point\PointPurchasing\Models\Service\Invoice as PurchaseServiceInvoice;
use Point\PointPurchasing\Models\Service\InvoiceItem as PurchaseServiceInvoiceItem;
use Point\PointPurchasing\Models\Service\PaymentOrder as PurchaseServicePaymentOrder;
use Point\PointPurchasing\Models\Service\PaymentOrderDetail as PurchaseServicePaymentOrderDetail;
use Point\PointSales\Models\Pos\Pos;
use Point\PointSales\Models\Pos\PosPricing;
use Point\PointSales\Models\Sales\DeliveryOrder;
use Point\PointSales\Models\Sales\DeliveryOrderItem;
use Point\PointSales\Models\Sales\Downpayment as SalesDownpayment;
use Point\PointSales\Models\Sales\Invoice as SalesInvoice;
use Point\PointSales\Models\Sales\InvoiceItem as SalesInvoiceItem;
use Point\PointSales\Models\Sales\PaymentCollection;
use Point\PointSales\Models\Sales\PaymentCollectionDetail;
use Point\PointSales\Models\Sales\Retur as SalesRetur;
use Point\PointSales\Models\Sales\ReturItem as SalesReturItem;
use Point\PointSales\Models\Sales\SalesOrder;
use Point\PointSales\Models\Sales\SalesOrderItem;
use Point\PointSales\Models\Sales\SalesQuotation;
use Point\PointSales\Models\Sales\SalesQuotationItem;
use Point\PointSales\Models\Service\Downpayment as SalesServiceDownpayment;
use Point\PointSales\Models\Service\Invoice as SalesServiceInvoice;
use Point\PointSales\Models\Service\InvoiceItem as SalesServiceInvoiceItem;
use Point\PointSales\Models\Service\PaymentCollection as ServicePaymentCollection;
use Point\PointSales\Models\Service\PaymentCollectionDetail as ServicePaymentCollectionDetail;

class ClearTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:clear-transaction {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all transaction';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // REMOVE ALL TRANSACTION
        if ($this->argument('type') == 'all') {
            self::removeAll();
        }

        // REMOVE EXPEDITION TRANSACTION
        if ($this->argument('type') == 'expedition') {
            self::removeExpedition();
        }

        // REMOVE SALES TRANSACTION
        if ($this->argument('type') == 'sales') {
            self::removeSales();
        }

        // REMOVE PURCHASE TRANSACTION
        if ($this->argument('type') == 'purchasing') {
            self::removePurchasing();
        }
    }

    public function removeAll()
    {
        \DB::table('formulir')->delete();
        \DB::table('formulir_lock')->delete();
        \DB::table('refer')->delete();
        \DB::table('temp')->delete();
        \DB::table('timeline')->delete();
    }

    public function removePurchasing()
    {
        $parent_class = array(
            get_class(new PurchaseOrder()),
            get_class(new PurchaseRequisition()),
            get_class(new PurchaseInvoice()),
            get_class(new GoodsReceived()),
            get_class(new PurchaseDownpayment()),
            get_class(new PurchasePaymentOrder()),
            get_class(new PurchaseRetur()),
            get_class(new PurchaseServicePaymentOrder()),
            get_class(new PurchaseServiceInvoice()),
            get_class(new PurchaseServiceDownpayment()),
            get_class(new FixedAssetsPurchaseOrder()),
            get_class(new FixedAssetsDownpayment()),
            get_class(new FixedAssetsGoodsReceived()),
            get_class(new FixedAssetsInvoice()),
            get_class(new FixedAssetsPaymentOrder()),
            get_class(new FixedAssetsPurchaseRequisition()),
            get_class(new FixedAssetsRetur()),
        );
        
        $child_class = array(
            get_class(new PurchaseRequisitionItem()),
            get_class(new PurchaseOrderItem()),
            get_class(new PurchaseInvoiceItem()),
            get_class(new GoodsReceivedItem()),
            get_class(new PurchaseReturItem()),
            get_class(new PurchasePaymentOrderDetail()),
            get_class(new PurchaseServiceInvoiceItem()),
            get_class(new PurchaseServicePaymentOrderDetail()),
            get_class(new FixedAssetsPurchaseOrderDetail()),
            get_class(new FixedAssetsGoodsReceivedDetail()),
            get_class(new FixedAssetsInvoiceDetail()),
            get_class(new FixedAssetsPaymentOrderDetail()),
            get_class(new FixedAssetsPurchaseRequisitionDetail()),
            get_class(new FixedAssetsReturItem()),
        );

        self::process($parent_class, $child_class);
    }

    public function removeSales()
    {
        $parent_class = array(
            get_class(new SalesQuotation()),
            get_class(new SalesOrder()),
            get_class(new SalesInvoice()),
            get_class(new DeliveryOrder()),
            get_class(new SalesDownpayment()),
            get_class(new PaymentCollection()),
            get_class(new SalesRetur()),
            get_class(new ServicePaymentCollection()),
            get_class(new SalesServiceInvoice()),
            get_class(new SalesServiceDownpayment()),
            get_class(new SalesServiceDownpayment()),
            get_class(new Pos()),
            get_class(new PosPricing()),
        );

        $child_class = array(
            get_class(new SalesQuotationItem()),
            get_class(new SalesOrderItem()),
            get_class(new SalesInvoiceItem()),
            get_class(new DeliveryOrderItem()),
            get_class(new PaymentCollectionDetail()),
            get_class(new SalesReturItem()),
            get_class(new ServicePaymentCollectionDetail()),
            get_class(new SalesServiceInvoiceItem()),
        );

        self::process($parent_class, $child_class);
    }

    public function removeExpedition()
    {
        $parent_class = array(
            get_class(new ExpeditionOrder()),
            get_class(new ExpeditionInvoice()),
            get_class(new ExpeditionDownpayment()),
            get_class(new ExpeditionPaymentOrder()),
        );

        $child_class = array(
            get_class(new ExpeditionOrderItem()),
            get_class(new ExpeditionInvoiceItem()),
            get_class(new ExpeditionPaymentOrderDetail()),
        );

        self::process($parent_class, $child_class);
    }

    public function process($parent_class = [], $child_class = [])
    {
        $class = array_merge($parent_class, $child_class);

        \DB::beginTransaction();

        $formulir = Formulir::whereIn('formulirable_type', $parent_class)->select('id')->get()->toArray();
        $formulir_lock = FormulirLock::whereIn('locked_id', $formulir)->select('locking_id')->get()->toArray();
        FormulirLock::whereIn('locked_id', $formulir)->delete();
        Formulir::whereIn('formulirable_type', $parent_class)->delete();
        Formulir::whereIn('id', $formulir_lock)->delete();
        Refer::whereIn('by_type', $class)->orWhereIn('to_type', $class)->orWhereIn('to_parent_type', $class)->delete();

        \DB::commit();
    }
}
