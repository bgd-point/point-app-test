<?php

namespace Point\PointPurchasing\Helpers\FixedAssets;

use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisition;
use Point\PointPurchasing\Models\FixedAssets\FixedAssetsPurchaseRequisitionDetail;

class FixedAssetsPurchaseRequisitionHelper
{
    public static function searchList($list_purchase_requisition, $status, $date_from, $date_to, $search)
    {
        $list_purchase_requisition = $list_purchase_requisition->where('formulir.form_status', '=', $status ?: 0);
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

    public static function create($request, $formulir)
    {
        $purchase_requisition = new FixedAssetsPurchaseRequisition;
        $purchase_requisition->formulir_id = $formulir->id;
        $purchase_requisition->employee_id = $request->input('employee_id');
        $purchase_requisition->supplier_id = $request->input('supplier_id') ? : null;
        $purchase_requisition->required_date = date_format_db($request->input('required_date'));
        $purchase_requisition->save();


        for ($i=0; $i< count($request->input('coa_id')); $i++) {
            $settle = new FixedAssetsPurchaseRequisitionDetail;
            $settle->fixed_assets_requisition_id = $purchase_requisition->id;
            $settle->coa_id = $request->input('coa_id')[$i];
            $settle->name = $request->input('name')[$i];
            $settle->quantity = number_format_db($request->input('quantity')[$i]);
            $settle->price = number_format_db($request->input('price')[$i]);
            $settle->unit = $request->input('unit')[$i];
            $settle->allocation_id = $request->input('allocation_id')[$i];
            $settle->save();
        }
        
        $formulir->approval_status = 0;
        $formulir->save();
        
        return $purchase_requisition;
    }

    public static function availableToOrder()
    {
        return FixedAssetsPurchaseRequisition::joinFormulir()
            ->joinEmployee()
            ->notArchived()
            ->where('formulir.form_status', '=', 0)
            ->where('formulir.approval_status', '=', 1)
            ->selectOriginal()
            ->paginate(100);
    }
}
