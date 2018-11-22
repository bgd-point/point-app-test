<?php

namespace Point\PointPurchasing\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\UserHelper;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\EmailHistory;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\PersonType;
use Point\PointPurchasing\Helpers\ServicePurchaseOrderHelper;
use Point\PointPurchasing\Http\Requests\ServicePurchaseOrderRequest;
use Point\PointPurchasing\Models\Service\PurchaseOrder;

class PurchaseOrderController extends Controller {
  use ValidationTrait;

  /**
   * @return mixed
   */
  public function index() {
    $view                      = view('point-purchasing::app.purchasing.point.service.purchase-order.index');
    $list_purchase_order       = PurchaseOrder::joinFormulir()->notArchived()->with('person')->selectOriginal();
    $list_purchase_order       = ServicePurchaseOrderHelper::searchList($list_purchase_order, \Input::get('order_by'), \Input::get('order_type'), \Input::get('status'), \Input::get('date_from'), \Input::get('date_to'), \Input::get('search'));
    $view->list_purchase_order = $list_purchase_order->paginate(100);

    $view->array_purchase_order_id = [];

    return $view;
  }

  /**
   * @return mixed
   */
  public function create() {
    $view                     = view('point-purchasing::app.purchasing.point.service.purchase-order.create');
    $view->list_person        = PersonHelper::getByType(['supplier']);
    $view->list_allocation    = Allocation::active()->get();
    $view->list_user_approval = UserHelper::getAllUser();
    $person_type              = PersonType::where('slug', 'supplier')->first();
    $view->list_group         = PersonGroup::where('person_type_id', '=', $person_type->id)->get();
    $view->code_contact       = PersonHelper::getCode($person_type);

    return $view;
  }

  /**
   * @param ServicePurchaseOrderRequest $request
   */
  public function store(ServicePurchaseOrderRequest $request) {
    DB::beginTransaction();
    FormulirHelper::isAllowedToCreate('create.point.purchasing.service.purchase.order', date_format_db($request->input('form_date'), $request->input('time')), []);

    $formulir       = FormulirHelper::create($request->input(), 'point-purchasing-service-purchase-order');
    $purchase_order = ServicePurchaseOrderHelper::create($request, $formulir);
    // timeline_publish('create.purchase-order', 'added new service purchase order ' . $purchase_order->formulir->form_number);

    DB::commit();

    gritter_success('create form success', 'false');

    return redirect('purchasing/point/service/purchase-order/' . $purchase_order->id);
  }

  /**
   * @param  $id
   * @return mixed
   */
  public function show($id) {
    $view                               = view('point-purchasing::app.purchasing.point.service.purchase-order.show');
    $view->purchase_order               = PurchaseOrder::find($id);
    $view->list_purchase_order_archived = PurchaseOrder::joinFormulir()->archived($view->purchase_order->formulir->form_number)->selectOriginal()->get();
    $view->revision                     = $view->list_purchase_order_archived->count();
    $view->list_reference               = FormulirLock::where('locking_id', '=', $view->purchase_order->formulir_id)->where('locked', true)->get();
    $view->email_history                = EmailHistory::where('formulir_id', $view->purchase_order->formulir_id)->get();

    return $view;
  }
}