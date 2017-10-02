<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Point\Core\Models\Vesa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Master\ItemUnit;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisition;
use Point\PointPurchasing\Models\Inventory\PurchaseRequisitionItem;
use Point\PointPurchasing\Models\Inventory\PurchaseOrder;
use Point\PointPurchasing\Models\Inventory\PurchaseOrderItem;

class PurchaseRequisitionHelper
{
    public static function searchList($list_purchase_requisition, $order_by, $order_type, $status = 0, $date_from, $date_to, $search)
    {
        if ($order_by) {
            $list_purchase_requisition = $list_purchase_requisition->orderBy($order_by, $order_type);
        } else {
            $list_purchase_requisition = $list_purchase_requisition->orderByStandard();
        }
        
        if ($status != 'all') {
            $list_purchase_requisition = $list_purchase_requisition->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($date_from) {
            $list_purchase_requisition = $list_purchase_requisition->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_purchase_requisition = $list_purchase_requisition->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_purchase_requisition = $list_purchase_requisition->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_purchase_requisition;
    }

    public static function create(Request $request, $formulir)
    {
        $purchase_requisition = new PurchaseRequisition;
        $purchase_requisition->formulir_id = $formulir->id;
        $purchase_requisition->employee_id = $request->input('employee_id');
        $purchase_requisition->supplier_id = $request->input('supplier_id') ? : null;
        $purchase_requisition->required_date = date_format_db($request->input('required_date'));
        $purchase_requisition->include_cash_advance = $request->input('include_cash_advance') ? 1 : 0 ;
        $purchase_requisition->save();

        for ($i=0 ; $i<count($request->input('item_id')) ; $i++) {
            $purchase_requisition_detail = new PurchaseRequisitionItem;
            $purchase_requisition_detail->point_purchasing_requisition_id = $purchase_requisition->id;
            $purchase_requisition_detail->item_id = $request->input('item_id')[$i];
            $purchase_requisition_detail->allocation_id = $request->input('allocation_id')[$i];
            $purchase_requisition_detail->quantity = number_format_db($request->input('item_quantity')[$i]);
            $purchase_requisition_detail->price = number_format_db($request->input('item_price')[$i]);
            $purchase_requisition_detail->unit = $request->input('item_unit')[$i];
            $purchase_requisition_detail->item_notes = $request->input('item_notes')[$i];
            $purchase_requisition_detail->converter = 1;
            $purchase_requisition_detail->save();
        }

        return $purchase_requisition;
    }
    
    public static function availableToOrder()
    {
        return PurchaseRequisition::joinFormulir()
            ->joinEmployee()
            ->notArchived()
            ->where('formulir.form_status', '=', 0)
            ->where('formulir.approval_status', '=', 1)
            ->selectOriginal()
            ->paginate(100);
    }
}
