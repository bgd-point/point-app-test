@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/purchase-order') }}">Purchase Order</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Purchase Order </h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active">
                        <a href="#block-tabs-home">Form</a>
                    </li>
                    <li>
                        <a href="#block-tabs-settings">
                            <i class="gi gi-settings"></i>
                        </a>
                    </li>
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
                                        'approval_status' => $purchase_order->formulir->approval_status,
                                        'approval_message' => $purchase_order->formulir->approval_message,
                                        'approval_at' => $purchase_order->formulir->approval_at,
                                        'approval_to' => $purchase_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $purchase_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend>
                                        <i class="fa fa-angle-right"></i> Formulir
                                    </legend>
                                </div>
                            </div>
                        </fieldset>
                        @if($revision)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>
                                <div class="col-md-6 content-show">{{ $revision }}</div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM NUMBER</label>
                            <div class="col-md-6 content-show">
                                {{ $purchase_order->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($purchase_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($purchase_order->person->id) !!}
                            </div>
                        </div>
                        @if($list_referenced->count() > 0)
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                    $model = $referenced->locking->formulirable_type;
                                    $url = $model::showUrl($referenced->locking->formulirable_id);
                                    ?>
                                    <div>
                                        <a href="{{ url($url) }}">
                                            {{ $referenced->locking->form_number }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {!! replace_links($purchase_order->formulir->notes) !!}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend>
                                        <i class="fa fa-angle-right"></i>
                                        Service
                                    </legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <!-- SERVICE DATA -->
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>SERVICE</th>
                                            <th>NOTES</th>
                                            <th>ALLOCATION</th>
                                            <th class="text-right">QUANTITY</th>
                                            <th class="text-right">PRICE</th>
                                            <th class="text-right">DISCOUNT (%)</th>
                                            <th class="text-right">TOTAL</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($purchase_order->services as $services)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('master/service/'.$services->service_id) }}">
                                                        {{ $services->service->name }}
                                                    </a>
                                                </td>
                                                <td>{{$services->service_notes}}</td>
                                                <td>
                                                    @if($services->allocation)
                                                        {{$services->allocation->name}}
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format_quantity($services->quantity) }} {{ $services->unit }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format_quantity($services->price) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format_quantity($services->discount) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format_quantity(($services->quantity * $services->price * ( 100 - $services->discount) / 100)) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table  class="table">
                                        <tr>
                                            <td style="width: 100%;" class="text-right">
                                                <strong>SUB TOTAL</strong>
                                            </td>
                                            <td class="text-right">
                                                {{ number_format_quantity($purchase_order->subtotal) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 100%;" class="text-right">
                                                <strong>DISCOUNT (%)</strong>
                                            </td>
                                            <td class="text-right">
                                                {{ number_format_quantity($purchase_order->discount) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 100%;" class="text-right">
                                                <strong>TAX BASE</strong>
                                            </td>
                                            <td class="text-right">
                                                {{ number_format_quantity($purchase_order->tax_base) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 100%;" class="text-right">
                                                <strong>TAX ({{$purchase_order->type_of_tax}})</strong>
                                            </td>
                                            <td class="text-right">
                                                {{ number_format_quantity($purchase_order->tax) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 100%;" class="text-right">
                                                <strong>TOTAL</strong></td>
                                            <td class="text-right">
                                                {{ number_format_quantity($purchase_order->total) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend>
                                        <i class="fa fa-angle-right"></i> Person In Charge
                                    </legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>
                                <div class="col-md-6 content-show">
                                    {{ $purchase_order->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>
                                <div class="col-md-6 content-show">
                                    {{ $purchase_order->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($purchase_order->formulir, 'update.point.purchasing.service.purchase.order'))
                                    <a href="{{url('purchasing/point/service/purchase-order/'.$purchase_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($purchase_order->formulir, 'delete.point.purchasing.service.purchase.order', 'approval.point.purchasing.service.purchase.order') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $purchase_order->formulir_id }}','approval.point.purchasing.service.purchase.order')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($purchase_order->formulir, 'delete.point.purchasing.service.purchase.order', 'approval.point.purchasing.service.purchase.order') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $purchase_order->formulir_id }}', 'delete.point.purchasing.service.purchase.order')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif
                                @if(formulir_view_email_vendor($purchase_order->formulir, 'create.point.purchasing.service.purchase.order'))
                                    <form action="{{url('purchasing/point/service/purchase-order/send-email')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="invoice_id" value="{{ $purchase_order->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email Supplier">
                                    </form>
                                    <a class="btn btn-effect-ripple btn-info"
                                    href="{{url('purchasing/point/service/purchase-order/'.$purchase_order->id.'/export')}}">Export PDF</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($purchase_order->formulir, 'approval.point.purchasing.service.purchase-order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/service/purchase-order/'.$purchase_order->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/service/purchase-order/'.$purchase_order->id.'/reject')}}"
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

                    @if($list_purchase_order_archived->count() > 0)
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
                                                <th>Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_purchase_order_archived as $purchase_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/service/purchase-order/'.$purchase_order_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->archived }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $purchase_order_archived->formulir->edit_notes }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($email_history->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> EMAIL HISTORY</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: right;">NO</th>
                                                    <th style="text-align: center;">SENT AT</th>
                                                    <th>SENDER</th>
                                                    <th>RECIPIENT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($email_history as $index=>$item)
                                                <tr>
                                                    <td style="text-align: right;">{{ $index+1 }}</td>
                                                    <td style="text-align: center;">{{ date_format_view($item->sent_at, true) }}</td>
                                                    <td><a href="/master/user/{{ $item->sender }}">{{ $item->user->name }}</a></td>
                                                    <td>{!! get_url_person($item->recipient) !!}
                                                        <span style="text-transform: none !important;"> ({{ $item->recipient_email }})</span>
                                                    </td>
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
        var item_table = initDatatable('#item-datatable');
    </script>
@stop
