<?php

namespace Point\PointPurchasing\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\PointPurchasing\Models\Service\PurchaseOrder;
use Point\PointPurchasing\Models\Service\PurchaseOrderDetail;

class ServicePurchaseOrderHelper {

  /**
   * @param  $list_purchase_order
   * @param  $order_by
   * @param  $order_type
   * @param  $status
   * @param  $date_from
   * @param  $date_to
   * @param  $search
   * @return mixed
   */
  public static function searchList($list_purchase_order, $order_by, $order_type, $status = 0, $date_from, $date_to, $search) {
    if ($order_by) {
      $list_purchase_order = $list_purchase_order->orderBy($order_by, $order_type);
    } else {
      $list_purchase_order = $list_purchase_order->orderByStandard();
    }

    if ($status != 'all') {
      $list_purchase_order = $list_purchase_order->where('formulir.form_status', '=', $status ?: 0);
    }

    if ($date_from) {
      $list_purchase_order = $list_purchase_order->where('form_date', '>=', date_format_db($date_from, 'start'));
    }

    if ($date_to) {
      $list_purchase_order = $list_purchase_order->where('form_date', '<=', date_format_db($date_to, 'end'));
    }

    if ($search) {
      // search input to database
      $list_purchase_order = $list_purchase_order->where(function ($q) use ($search) {
        $q->where('person.name', 'like', '%' . $search . '%')
          ->orWhere('formulir.form_number', 'like', '%' . $search . '%');
      });
    }

    return $list_purchase_order;
  }

  /**
   * @param  Request      $request
   * @param  $formulir
   * @return mixed
   */
  public static function create(Request $request, $formulir) {
    DB::beginTransaction();

    $purchase_order              = new PurchaseOrder();
    $purchase_order->formulir_id = $formulir->id;
    $purchase_order->person_id   = $request->input('person_id');
    $purchase_order->type_of_tax = $request->input('type_of_tax');
    $purchase_order->is_cash     = $request->input('is_cash') ? true : false;
    $subtotal                    = 0;

    $purchase_order_detail = [];
    for ($i = 0; $i < count($request->input('service_id')); $i++) {
      $detail                = new PurchaseOrderDetail();
      $detail->service_id    = $request->input('service_id')[$i];
      $detail->allocation_id = $request->input('service_allocation_id')[$i];
      $detail->quantity      = number_format_db($request->input('service_quantity')[$i]);
      $detail->price         = number_format_db($request->input('service_price')[$i]);
      $detail->discount      = number_format_db($request->input('service_discount')[$i]);

      array_push($purchase_order_detail, $detail->toArray());

      $price_before_discount = $detail->quantity * $detail->price;
      $price_after_discount  = $price_before_discount * (100 - $detail->discount) / 100;
      $subtotal += $price_after_discount;
    }

    $discount = number_format_db($request->input('discount'));
    $tax_base = $subtotal - ($subtotal / 100 * $discount);
    $tax      = 0;

    if ($request->input('type_of_tax') === 'exclude') {
      $tax = $tax_base * 11 / 100;
    } else if ($request->input('type_of_tax') === 'include') {
      $tax_base = $tax_base * 100 / 111;
      $tax      = $tax_base * 11 / 100;
    }

    $purchase_order->subtotal = $subtotal;
    $purchase_order->discount = $discount;
    $purchase_order->tax_base = $tax_base;
    $purchase_order->tax      = $tax;
    $purchase_order->total    = $tax_base + $tax;
    $purchase_order->save();

    foreach ($purchase_order_detail as $key => $detail) {
      $purchase_order_detail[$key]['purchase_order_id'] = $purchase_order->id;
    }
    PurchaseOrderDetail::insert($purchase_order_detail);

    $formulir->approval_to     = $request->input('approval_to');
    $formulir->approval_status = 0;
    $formulir->save();

    DB::commit();

    return $purchase_order;
  }
}
