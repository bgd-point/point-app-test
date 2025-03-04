<?php

namespace Point\PointExpedition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Point\Core\Helpers\QueueHelper;
use Point\Core\Helpers\UserHelper;
use Point\Core\Models\Master\Permission;
use Point\Core\Models\User;
use Point\Core\Traits\ValidationTrait;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\PersonHelper;
use Point\Framework\Models\FormulirLock;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\PersonGroup;
use Point\Framework\Models\Master\UserWarehouse;
use Point\Framework\Models\Master\Warehouse;
use Point\PointExpedition\Helpers\ExpeditionOrderHelper;
use Point\PointExpedition\Models\ExpeditionOrder;
use Point\PointExpedition\Models\ExpeditionOrderItem;
use Point\PointExpedition\Models\ExpeditionOrderReference;

class ExpeditionOrderController extends Controller
{
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        access_is_allowed('read.point.expedition.order');

        $list_expedition_order = ExpeditionOrder::joinFormulir()
            ->joinExpedition()
            ->notArchived()
            ->selectOriginal();

        $list_expedition_order = ExpeditionOrderHelper::searchList(
            $list_expedition_order,
            app('request')->input('status'),
            app('request')->input('order_by'),
            app('request')->input('status'),
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        );

        $view = view('point-expedition::app.expedition.point.expedition-order.index');
        $view->list_expedition_order = $list_expedition_order->paginate(100);
        return $view;
    }


    public function indexPDF(Request $request)
    {
        access_is_allowed('read.point.expedition.order');
        $list_expedition_order = ExpeditionOrder::joinFormulir()->joinExpedition()->notArchived()->selectOriginal();
        $list_expedition_order = ExpeditionOrderHelper::searchList(
            $list_expedition_order,
            app('request')->input('status'),
            app('request')->input('order_by'),
            app('request')->input('status'),
            app('request')->input('date_from'),
            app('request')->input('date_to'),
            app('request')->input('search')
        )->get();

        $pdf = \PDF::loadView('point-expedition::app.expedition.point.expedition-order.index-pdf', ['list_expedition_order' => $list_expedition_order]);
        return $pdf->stream();
    }

    public function createStep1()
    {
        access_is_allowed('create.point.expedition.order');

        $view = view('point-expedition::app.expedition.point.expedition-order.create-step-1');
        // $array_expedition_reference_id_open = ExpeditionOrder::getExpeditionReferenceIsOpen();
        $view->expedition_collection = ExpeditionOrderReference::joinPerson()->where('finish', 0)->selectOriginal()->paginate(100);
        return $view;
    }

    public function createStep2($formulir_id)
    {
        access_is_allowed('create.point.expedition.order');

        $view = view('point-expedition::app.expedition.point.expedition-order.create-step-2');
        $view->reference = ExpeditionOrderReference::where('expedition_reference_id', $formulir_id)->first();
        $view->expedition_reference = \Input::get('group') ? ExpeditionOrder::find(\Input::get('group')) : $view->reference;
        $view->list_expedition = PersonHelper::getByType(['expedition']);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->person_type = PersonHelper::getType('expedition');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        return $view;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'approval_to' => 'required',
            'total' => 'required',
        ]);

        if (! $request->input('total') > 0) {
            gritter_error('column total can not empty');
            return redirect('expedition/point/expedition-order/create-step-2/' . $request->input('reference_id'));
        }

        DB::beginTransaction();

        $reference_type = $request->input('reference_type');
        $reference_id = $request->input('reference_id');
        $reference = $reference_type::find($reference_id);

        FormulirHelper::isAllowedToCreate('create.point.expedition.order', date_format_db($request->input('form_date'), $request->input('time')), [$reference->formulir_id]);
        $formulir = FormulirHelper::create($request->input(), 'point-expedition-order');
        $expedition_order = ExpeditionOrderHelper::create($request, $formulir);
        timeline_publish('create.expedition.order', 'added new expedition order ' . $expedition_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/expedition-order/' . $expedition_order->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        access_is_allowed('read.point.expedition.order');
        $view = view('point-expedition::app.expedition.point.expedition-order.show');
        $view->expedition_order = ExpeditionOrder::find($id);
        $view->reference = FormulirHelper::getLockedModel($view->expedition_order->formulir_id);
        $view->expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $view->reference->formulir_id)->first();
        $view->list_expedition_order_archived = ExpeditionOrder::joinFormulir()->archived($view->expedition_order->formulir->form_number)->selectOriginal()->get();
        $view->revision = $view->list_expedition_order_archived->count();
        if (!$view->expedition_order->formulir->form_number) {
            return redirect(ExpeditionOrder::showUrl($id));
        }

        $view->list_referenced = FormulirLock::where('locked_id', '=', $view->expedition_order->formulir_id)->where('locked', true)->get();

        return $view;
    }

    public function archived($id)
    {
        access_is_allowed('read.point.expedition.order');

        $view = view('point-expedition::app.expedition.point.expedition-order.archived');
        $view->expedition_order_archived = ExpeditionOrder::find($id);
        $view->expedition_order = ExpeditionOrder::joinFormulir()->notArchived($view->expedition_order_archived->archived)->selectOriginal()->first();
        $view->expedition_order->tax_percentage = $view->expedition_order->tax / $view->expedition_order->tax_base * 100;
        $view->reference = FormulirHelper::getLockedModel($view->expedition_order_archived->formulir_id);
        $view->expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $view->reference->formulir_id)->first();
        return $view;
    }

    public function edit($id)
    {
        access_is_allowed('update.point.expedition.order');

        $view = view('point-expedition::app.expedition.point.expedition-order.edit');

        $view->expedition_order = ExpeditionOrder::find($id);
        $view->expedition_order_detail = ExpeditionOrderItem::where('point_expedition_order_id', $id)->get();
        $view->list_expedition = PersonHelper::getByType(['expedition']);
        $view->reference = FormulirHelper::getLockedModel($view->expedition_order->formulir_id);
        $view->list_user_approval = UserHelper::getAllUser();
        $view->expedition_reference = ExpeditionOrderReference::where('expedition_reference_id', $view->reference->formulir_id)->first();
        $view->person_type = PersonHelper::getType('expedition');
        $view->list_group = PersonGroup::where('person_type_id', '=', $view->person_type->id)->get();
        $view->code_contact = PersonHelper::getCode($view->person_type);
        
        return $view;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'form_date' => 'required',
            'expedition_id' => 'required',
            'approval_to' => 'required',
            'total' => 'required',
        ]);

        $expedition_order = ExpeditionOrder::find($id);
        FormulirHelper::isAllowedToUpdate('update.point.expedition.order', date_format_db($request->input('form_date'), $request->input('time')), $expedition_order->formulir);

        DB::beginTransaction();

        $formulir_old = FormulirHelper::archive($request->input(), $expedition_order->formulir_id);
        $formulir = FormulirHelper::update($request->input(), $formulir_old->archived, $formulir_old->form_raw_number);
        $expedition_order = ExpeditionOrderHelper::create($request, $formulir);
        timeline_publish('update.expedition.order', 'update expedition order ' . $expedition_order->formulir->form_number);

        DB::commit();

        gritter_success('create form success');
        return redirect('expedition/point/expedition-order/' . $expedition_order->id);
    }

    public function sendEmailOrder(Request $request)
    {
        $id = app('request')->input('expedition_order_id');
        $expedition_order = ExpeditionOrder::joinExpedition()->where('point_expedition_order.id', $id)->select('point_expedition_order.*')->first();
        $request = $request->input();
        $token = md5(date('ymdhis'));
        $warehouse = '';
        $warehouse_id = UserWarehouse::getWarehouse(auth()->user()->id);
        if ($warehouse_id > 0) {
            $warehouse = Warehouse::find($warehouse_id);
        }

        if (! $expedition_order) {
            gritter_error('Failed, please select expedition order', 'false');
            return redirect()->back();
        }

        if (! $expedition_order->expedition->email) {
            gritter_error('Failed, please add email for expedition', 'false');
            return redirect()->back();
        }

        $data = array(
            'expedition_order' => $expedition_order,
            'token' => $token,
            'warehouse' => $warehouse
        );
        
        $name = 'EXPEDITION ORDER '. $expedition_order->formulir->form_number;

        \Queue::push(function ($job) use ($data, $request, $expedition_order, $warehouse, $name) {
            QueueHelper::reconnectAppDatabase($request['database_name']);
            \Mail::send('point-expedition::emails.expedition.point.external.expedition-order', $data, function ($message) use ($expedition_order, $warehouse, $data, $name) {
                $message->to($expedition_order->expedition->email)->subject($name);
                $pdf = \PDF::loadView('point-expedition::emails.expedition.point.external.expedition-order-pdf', $data)->setPaper('a4', 'landscape');
                $message->attachData($pdf->output(), $name. ".pdf");
            });
            $job->delete();
        });

        gritter_success('Success send email expedition order', 'false');
        return redirect()->back();
    }
}
