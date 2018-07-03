@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/delivery-order') }}">Delivery Order</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Delivery Order</h2>
        @include('point-sales::app.sales.point.sales.delivery-order._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">

                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._approval_status_label', [
                                        'approval_status' => $delivery_order->formulir->approval_status,
                                        'approval_message' => $delivery_order->formulir->approval_message,
                                        'approval_at' => $delivery_order->formulir->approval_at,
                                        'approval_to' => $delivery_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $delivery_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Info Reference</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Date</label>

                                <div class="col-md-6 content-show">
                                    {{ date_format_view($reference->formulir->form_date, true) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Number</label>
                                <div class="col-md-6 content-show">
                                    <a target="_blank" href="{{url('sales/point/indirect/sales-order/'.$reference->id)}}">
                                    {{ $reference->formulir->form_number }}
                                    </a>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Customer</label>
                                <div class="col-md-6 content-show">
                                    {!! get_url_person($reference->person->id) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Remaining Downpayment</label>
                                <div class="col-md-6 content-show">
                                    <?php $remaining_downpayment = $reference->getTotalRemainingDownpayment($reference->id);?>
                                    {{ number_format_price($remaining_downpayment) }}
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Delivery Order Form</legend>
                                </div>
                            </div>
                        </fieldset>
                        @if($revision)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>

                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                {{ $delivery_order->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($delivery_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Warehouse</label>

                            <div class="col-md-6 content-show">
                                {{ $delivery_order->warehouse->codeName }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Driver</label>

                            <div class="col-md-6 content-show">
                                {{ $delivery_order->driver }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">License Plate</label>

                            <div class="col-md-6 content-show">
                                {{ $delivery_order->license_plate }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $delivery_order->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                    $model = $referenced->locking->formulirable_type;
                                    $url = $model::showUrl($referenced->locking->formulirable_id);
                                    ?>
                                    <a href="{{ url($url) }}">{{ $referenced->locking->form_number }}</a> <br/>
                                @endforeach
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Item</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="item-datatable" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>ITEM</th>
                                                <th class="text-right">QUANTITY</th>
                                                <th>UNIT</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($delivery_order->items as $delivery_order_item)
                                                <tr>
                                                    <td>{{ $delivery_order_item->item->codeName }}</td>
                                                    <td class="text-right">{{ number_format_quantity($delivery_order_item->quantity) }}</td>
                                                    <td>{{ $delivery_order_item->unit }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $delivery_order->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>

                                <div class="col-md-6 content-show">
                                    {{ $delivery_order->formulir->approvalTo->name }}
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="tab-pane" id="block-tabs-settings">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Action</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                @if(formulir_view_edit($delivery_order->formulir, 'update.point.sales.delivery.order'))
                                    <a href="{{url('sales/point/indirect/delivery-order/'.$delivery_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($delivery_order->formulir, 'delete.point.sales.delivery.order', 'approval.point.sales.delivery.order') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $delivery_order->formulir_id }}','approval.point.sales.delivery.order')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($delivery_order->formulir, 'delete.point.sales.delivery.order', 'approval.point.sales.delivery.order') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm('{{url('formulir/requestCancel')}}', '{{ $delivery_order->formulir_id }}', 'delete.point.sales.delivery.order')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($delivery_order->formulir, 'update.point.sales.delivery.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$delivery_order->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($delivery_order->formulir, 'update.point.sales.delivery.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$delivery_order->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($delivery_order->formulir, 'create.point.sales.invoice'))
                                    <a class="btn btn-effect-ripple btn-info"
                                            href="{{url('sales/point/indirect/delivery-order/'.$delivery_order->id.'/export')}}">Print</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($delivery_order->formulir, 'approval.point.sales.delivery.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/delivery-order/'.$delivery_order->id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group">
                                        <input type="text" name="approval_message" class="form-control"
                                               placeholder="Message">
                                        <span class="input-group-btn">
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                        </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/delivery-order/'.$delivery_order->id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group">
                                        <input type="text" name="approval_message" class="form-control"
                                               placeholder="Message">
                                        <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                        </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($list_delivery_order_archived->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Form Date</th>
                                                <th>Form Number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_delivery_order_archived as $delivery_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('sales/point/indirect/delivery-order/'.$delivery_order_archived->formulirable_id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($delivery_order->formulir->form_date) }}</td>
                                                    <td>{{ $delivery_order_archived->formulir->archived }}</td>
                                                    <td>{{ $delivery_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $delivery_order_archived->formulir->updatedBy->name }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
